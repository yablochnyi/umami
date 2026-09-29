<?php

namespace App\Services\GoPos;

use App\Models\AnalyticsOrder;
use App\Models\AnalyticsSyncRun;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GoPosAnalyticsSync
{
    public function __construct(private GoPosClient $client) {}

    public function sync(string $from): AnalyticsSyncRun
    {
        $start = CarbonImmutable::createFromFormat('!Y-m-d', $from, 'Europe/Warsaw');
        if (! $start || $start->format('Y-m-d') !== $from || $start->isFuture()) {
            throw new RuntimeException('Invalid analytics start date.');
        }
        $run = AnalyticsSyncRun::create(['status' => 'running', 'from_date' => $from, 'started_at' => now()]);
        try {
            $organization = $this->client->organizationId();
            $path = "/api/v3/{$organization}/orders";
            $query = ['created_at_from' => $start->format('Y-m-d\TH:i:s'), 'created_at_to' => now('Europe/Warsaw')->format('Y-m-d\TH:i:s')];
            $latest = $this->fetch($path, $query + ['size' => 1, 'sort' => 'id,desc']);
            $ceiling = (int) ($latest[0]['id'] ?? 0);
            $cursor = 0;
            $seen = 0;
            // Keyset pagination keeps a fixed upper bound while new orders arrive.
            while ($cursor < $ceiling) {
                $orders = $this->fetch($path, $query + ['page' => 0, 'size' => 100, 'sort' => 'id,asc', 'id_from' => $cursor, 'id_to' => $ceiling, 'include' => 'items,items.product,transactions']);
                if ($orders === []) {
                    throw new RuntimeException('GoPOS pagination stopped before the snapshot boundary.');
                }
                foreach ($orders as $order) {
                    $id = (int) ($order['id'] ?? 0);
                    if ($id <= $cursor || $id > $ceiling) {
                        throw new RuntimeException('Unexpected GoPOS pagination order.');
                    }
                    $ordered = CarbonImmutable::parse($order['created_at'], 'Europe/Warsaw')->setTimezone('Europe/Warsaw');
                    if ($ordered->lt($start)) {
                        throw new RuntimeException('GoPOS ignored the requested date range.');
                    }
                    $this->store($organization, $order, $ordered);
                    $cursor = $id;
                    $seen++;
                }
                $run->update(['orders_count' => $seen]);
                usleep(100000);
            }
            $run->update(['status' => 'completed', 'finished_at' => now()]);

            return $run;
        } catch (\Throwable $e) {
            // API response bodies can contain customer data; do not persist them in the UI log.
            $run->update(['status' => 'failed', 'finished_at' => now(), 'error' => class_basename($e)]);
            throw $e;
        }
    }

    private function fetch(string $path, array $query): array
    {
        $payload = retry(3, fn () => $this->client->get($path, $query), 1500);
        if (! isset($payload['data']) || ! is_array($payload['data'])) {
            throw new RuntimeException('Invalid GoPOS orders response.');
        }

        return $payload['data'];
    }

    private function cents(array $value): int
    {
        if (! isset($value['amount']) || ! is_numeric($value['amount'])) {
            throw new RuntimeException('Missing GoPOS monetary amount.');
        }

        return (int) round((float) $value['amount'] * 100);
    }

    private function store(string $organization, array $order, CarbonImmutable $ordered): void
    {
        if (! isset($order['items']) || ! is_array($order['items'])) {
            throw new RuntimeException('GoPOS did not include order items.');
        }
        DB::transaction(function () use ($organization, $order, $ordered): void {
            $record = AnalyticsOrder::updateOrCreate(['organization_id' => $organization, 'remote_id' => $order['id']], [
                'number' => $order['number'] ?? (string) $order['id'],
                'ordered_at' => $ordered->utc(), 'business_date' => $ordered->format('Y-m-d'),
                'hour' => $ordered->hour, 'weekday' => $ordered->isoWeekday(),
                'source' => trim($order['source'] ?? '') ?: 'Unknown',
                'status' => $order['status'], 'payment_status' => $order['payment_status'],
                'order_type' => $order['type'], 'currency' => $order['total_price']['currency'],
                'total_cents' => $this->cents($order['total_price']),
                'paid_cents' => $this->cents($order['total_paid_price']),
                'net_cents' => isset($order['total_price_net']) ? $this->cents($order['total_price_net']) : null,
                'payments' => array_map(fn ($payment) => [
                    'method' => $payment['payment_method_name'] ?? 'Unknown',
                    'status' => $payment['status'] ?? 'Unknown',
                    'amount_cents' => $this->cents($payment['price']),
                ], $order['transactions'] ?? []),
                'synced_at' => now(),
            ]);
            // Replace a snapshot atomically so corrections and removed lines do not accumulate.
            $record->items()->delete();
            foreach ($order['items'] as $item) {
                if (($item['status'] ?? 'ACTIVE') !== 'ACTIVE') {
                    continue;
                }
                $record->items()->create([
                    'remote_item_id' => $item['item_id'] ?? null, 'name' => $item['name'] ?? ('GoPOS #'.($item['item_id'] ?? $item['id'])),
                    'quantity' => $item['quantity'], 'total_cents' => $this->cents($item['total_price']),
                ]);
            }
        });
    }
}

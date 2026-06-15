<?php

namespace App\Console\Commands;

use App\Models\MenuItem;
use App\Services\GoPos\GoPosClient;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Str;

class SendGoPosTestOrderCommand extends Command
{
    protected $signature = 'gopos:send-test-order
        {--send : Actually send the test order to GoPOS}
        {--item= : Local menu item ID or GoPOS item ID}
        {--quantity=1 : Test item quantity}
        {--name=Test Umami : Test customer name}
        {--phone=+48500100100 : Test customer phone}
        {--email=test@umamisushifood.pl : Test customer email}';

    protected $description = 'Build or send a test pickup order to GoPOS';

    public function handle(GoPosClient $goPos): int
    {
        $item = $this->resolveItem();

        if (! $item) {
            $this->error('No active menu item with gopos_id was found. Run php artisan gopos:sync-menu first.');

            return self::FAILURE;
        }

        $quantity = max(1, (float) $this->option('quantity'));
        $referenceId = 'umami-test-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(5));
        $payload = $this->payload($item, $quantity, $referenceId);

        $this->line('Test order payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if (! $this->option('send')) {
            $this->newLine();
            $this->warn('Order was not sent. To send it, run:');
            $this->line('php artisan gopos:send-test-order --send');

            return self::SUCCESS;
        }

        try {
            $organizationId = $goPos->organizationId();
            $response = $goPos->post("/api/v3/{$organizationId}/orders", $payload);
            $order = $response['data'] ?? $response;

            $this->newLine();
            $this->info('Test order sent to GoPOS.');
            $this->table(['Field', 'Value'], [
                ['id', $order['id'] ?? '-'],
                ['uid', $order['uid'] ?? '-'],
                ['number', $order['number'] ?? '-'],
                ['reference_id', $order['reference_id'] ?? $referenceId],
                ['status', $order['status'] ?? '-'],
            ]);

            return self::SUCCESS;
        } catch (RequestException $exception) {
            $this->error('GoPOS rejected the test order.');
            $this->line($exception->response?->body() ?: $exception->getMessage());

            return self::FAILURE;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function resolveItem(): ?MenuItem
    {
        $query = MenuItem::query()
            ->where('is_active', true)
            ->whereNotNull('gopos_id');

        if ($itemId = $this->option('item')) {
            return (clone $query)
                ->where(fn ($query) => $query
                    ->where('id', $itemId)
                    ->orWhere('gopos_id', $itemId))
                ->first();
        }

        return (clone $query)
            ->where(fn ($query) => $query
                ->where('slug', 'edamame')
                ->orWhere('name->pl', 'Edamame'))
            ->first()
            ?: $query->orderBy('sort_order')->first();
    }

    private function payload(MenuItem $item, float $quantity, string $referenceId): array
    {
        $price = data_get($item->gopos_payload, 'price.amount');
        $taxId = $item->gopos_tax_id;
        $itemPayload = [
            'quantity' => $quantity,
            'item_id' => (int) $item->gopos_id,
            'comment' => 'TEST ze strony www',
        ];

        if ($price !== null) {
            $itemPayload['unit_price'] = [
                'amount' => (float) $price,
                'currency' => data_get($item->gopos_payload, 'price.currency', 'PLN'),
            ];
        }

        if ($taxId) {
            $itemPayload['tax'] = ['id' => (int) $taxId];
        }

        return [
            'type' => 'PICK_UP',
            'source' => 'UMAMI_WWW',
            'source_number' => $referenceId,
            'reference_id' => $referenceId,
            'comment' => 'TEST - zamówienie techniczne z integracji strony. Prosimy nie realizować.',
            'estimated_delivery_at' => now()->addMinutes(45)->format('Y-m-d\TH:i:s'),
            'items' => [$itemPayload],
            'contact' => [
                'name' => (string) $this->option('name'),
                'phone_number' => (string) $this->option('phone'),
                'email' => (string) $this->option('email'),
                'source' => 'UMAMI_WWW',
            ],
            'fiscalization' => [
                'status' => 'NOT_FISCALIZED',
                'type' => 'NONE',
                'email' => (string) $this->option('email'),
            ],
            'custom_fields' => [
                'integration_test' => 'true',
                'local_menu_item_id' => (string) $item->id,
            ],
        ];
    }
}

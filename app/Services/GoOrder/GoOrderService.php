<?php

namespace App\Services\GoOrder;

use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class GoOrderService
{
    public function __construct(private GoOrderClient $client) {}

    public function send(Order $order): void
    {
        if (! config('goorder.enabled')) {
            return;
        }
        Cache::lock('goorder:order:'.$order->id, 120)->block(5, function () use ($order) {
            $order->refresh();
            if ($order->status !== 'new') {
                return;
            }
            $order->update(['status' => 'preparing_goorder']);
            try {
                $checkout = $this->checkout($order);
                $order->update(['goorder_checkout' => $checkout]);
                $remote = $this->client->request('POST', 'orders', $checkout);
                if (empty($remote['id']) || empty($remote['token'])) {
                    throw new RuntimeException('goorder_missing_identity');
                }
                // Persist the remote identity before any request that submits to the kitchen.
                $order->update([
                    'goorder_id' => (string) $remote['id'],
                    'goorder_token' => $remote['token'],
                ]);
                if (($remote['status'] ?? null) !== 'OPEN') {
                    $order->update(['goorder_submit_started_at' => now()]);
                    throw new RuntimeException('goorder_unexpected_draft_status');
                }
                $quote = $this->quote($remote);
                $order->update(['goorder_quote' => $quote, 'status' => 'quote_review']);
                if ($this->matchesLocalQuote($order, $quote)) {
                    $this->submitDraft($order, $quote);
                }
            } catch (Throwable $exception) {
                $this->recordFailure($order, $exception);
            }
        });
    }

    public function confirm(Order $order, string $quoteHash): void
    {
        Cache::lock('goorder:order:'.$order->id, 120)->block(5, function () use ($order, $quoteHash) {
            $order->refresh();
            if ($order->status !== 'quote_review' || $order->goorder_submit_started_at) {
                return;
            }
            try {
                if (! hash_equals($this->quoteHash($order->goorder_quote), $quoteHash)) {
                    return;
                }
                $this->submitDraft($order, $order->goorder_quote);
            } catch (Throwable $exception) {
                $this->recordFailure($order, $exception);
            }
        });
    }

    private function checkout(Order $order): array
    {
        $config = $this->client->request('GET', 'config');
        $ordering = $config['ordering'] ?? [];
        $type = $order->delivery_type === 'delivery' ? 'DELIVERY' : 'PICK_UP';
        if (($ordering['mode'] ?? null) !== 'ENABLED' || empty($ordering[$type === 'DELIVERY' ? 'delivery' : 'takeaway'])) {
            throw new RuntimeException('goorder_unavailable');
        }
        // New GoOrder consent requirements must be surfaced, never silently accepted.
        if (! empty($ordering['rules'])) {
            throw new RuntimeException('goorder_rules_require_setup');
        }
        $paymentId = config('goorder.payment_methods.'.$order->payment_type);
        $payment = collect($ordering['payment_methods'] ?? [])->firstWhere('id', $paymentId);
        if (! $payment || (! empty($payment['order_types']) && ! in_array($type, $payment['order_types'], true))) {
            throw new RuntimeException('goorder_payment_unavailable');
        }
        $menu = $this->client->menu();
        $method = collect($menu['payment_methods'] ?? [])->firstWhere('id', $paymentId);
        if (! $method || ($method['status'] ?? '') !== 'ENABLED' || ! empty($method['settings'])
            || ($method['reference_id'] ?? null) !== config('goorder.payment_references.'.$order->payment_type)) {
            throw new RuntimeException('goorder_offline_payment_requires_setup');
        }
        $published = collect(app(GoOrderCatalog::class)->parse($menu))->flatMap(fn ($section) => $section['published'] ? $section['items'] : [])->keyBy('item.id');
        $items = $order->items()->with('menuItem.category')->get()->map(function ($item) use ($menu, $type, $order, $published) {
            $local = $item->menuItem;
            $remote = $local?->goorder_id ? collect($menu['items'] ?? [])->firstWhere('id', $local->goorder_id) : null;
            if (! $remote || ($remote['reference_id'] ?? null) !== $local->goorder_reference_id) {
                throw new RuntimeException('goorder_item_unmapped');
            }
            if (! isset($published[$remote['id']])) {
                throw new RuntimeException('goorder_item_unavailable');
            }
            $local->goorder_rules = $published[$remote['id']]['rules'];
            $local->goorder_payload = $remote;
            if (! app(MenuAvailability::class)->check($local, $order->fulfillment_type === 'scheduled' ? $order->scheduled_at : null, $type)) {
                throw new RuntimeException('goorder_item_unavailable');
            }
            if (($remote['status'] ?? null) !== 'ENABLED'
                || (! empty($remote['order_types']) && ! in_array($type, $remote['order_types'], true))) {
                throw new RuntimeException('goorder_item_unavailable');
            }
            try {
                $choices = app(GoOrderModifiers::class)->choices($remote, $menu);
                $saved = $item->payload['modifiers'] ?? [];
                $selected = [];
                foreach ($saved as $selection) {
                    $selected[$selection['group_id']] = $selection['option']['id'];
                }
                $selections = app(GoOrderModifiers::class)->validate($choices, $selected);
                foreach ($selections as $selection) {
                    $previous = collect($saved)->firstWhere('group_id', $selection['group_id']);
                    if (($previous['option']['reference_id'] ?? null) !== $selection['option']['reference_id']) {
                        throw new RuntimeException('goorder_modifier_identity_changed');
                    }
                }
            } catch (\Throwable) {
                throw new RuntimeException('goorder_modifiers_require_setup');
            }

            return ['item_id' => $remote['id'], 'quantity' => $item->quantity, 'modifier_groups' => app(GoOrderModifiers::class)->payload($selections)];
        })->all();
        $customer = $order->customer;

        return [
            'type' => $type,
            'contact' => $order->goorder_checkout['contact'] ?? ['name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone],
            'address' => $type === 'DELIVERY' ? [
                'city' => $order->city, 'street' => $order->street, 'build_nr' => $order->building_number,
                'flat_nr' => $order->apartment_number, 'country' => 'PL',
            ] : null,
            'pickup_at' => $order->fulfillment_type === 'scheduled'
                ? $order->scheduled_at?->timezone('Europe/Warsaw')->format('Y-m-d\TH:i:s') : null,
            'comment' => trim($order->number.' '.($order->comment ?? '')),
            'tax_id_no' => $order->wants_invoice ? $order->nip : null,
            'payment_method_id' => $paymentId,
            'accepted_rules' => [],
            'items' => $items,
        ];
    }

    private function submitDraft(Order $order, array $approvedQuote): void
    {
        if (! config('goorder.enabled')) {
            throw new RuntimeException('goorder_disabled');
        }
        foreach ($order->items()->with('menuItem.category')->get() as $line) {
            if (! $line->menuItem || ! app(MenuAvailability::class)->check($line->menuItem,
                $order->fulfillment_type === 'scheduled' ? $order->scheduled_at : null,
                $order->delivery_type === 'delivery' ? 'DELIVERY' : 'PICK_UP')) {
                throw new RuntimeException('goorder_item_unavailable');
            }
        }
        $remote = $this->client->order($order);
        if (($remote['status'] ?? null) !== 'OPEN') {
            $this->applyStatus($order, $remote);

            return;
        }
        $freshQuote = $this->quote($remote);
        foreach ($order->goorder_checkout['items'] as $requested) {
            $lines = collect($freshQuote['items'])->where('item_id', $requested['item_id']);
            if ($lines->count() !== 1 || (float) $lines->first()['quantity'] !== (float) $requested['quantity']) {
                throw new RuntimeException('goorder_quote_items_changed');
            }
        }
        if ($this->quoteHash($freshQuote) !== $this->quoteHash($approvedQuote)) {
            $order->update(['goorder_quote' => $freshQuote, 'status' => 'quote_review']);

            return;
        }
        // This durable marker prevents a second /pay even if the first response is lost.
        $order->update([
            'status' => 'submitting_goorder', 'goorder_submit_started_at' => now(),
            'total' => $freshQuote['total'] / 100, 'delivery_cost' => $freshQuote['delivery'] / 100,
            'subtotal' => ($freshQuote['total'] - $freshQuote['delivery']) / 100,
        ]);
        foreach ($order->items()->with('menuItem')->get() as $item) {
            $line = collect($freshQuote['items'])->firstWhere('item_id', $item->menuItem?->goorder_id);
            if ($line) {
                $item->update(['total' => $line['total'] / 100, 'unit_price' => $line['total'] / 100 / $item->quantity]);
            }
        }
        $checkout = $order->goorder_checkout;
        unset($checkout['items']);
        $remote = $this->client->request('POST', 'orders/'.rawurlencode($order->goorder_id).'/pay', $checkout, $order);
        $this->applyStatus($order, $remote);
    }

    private function matchesLocalQuote(Order $order, array $quote): bool
    {
        if ($quote['total'] !== (int) round((float) $order->total * 100)
            || $quote['delivery'] !== (int) round((float) $order->delivery_cost * 100)
            || count($quote['items']) !== $order->items->count()) {
            return false;
        }
        foreach ($order->items as $item) {
            $line = collect($quote['items'])->firstWhere('item_id', $item->menuItem->goorder_id);
            if (! $line || (int) $line['quantity'] !== $item->quantity || $line['total'] !== (int) round((float) $item->total * 100)) {
                return false;
            }
        }

        return true;
    }

    public function sync(Order $order): void
    {
        if (! $order->goorder_id || $order->trackingFinished() || $order->status === 'quote_review') {
            return;
        }
        Cache::lock('goorder:order:'.$order->id, 120)->get(function () use ($order) {
            $order->refresh();
            if ($order->goorder_synced_at?->gt(now()->subSeconds(config('goorder.poll_seconds')))) {
                return;
            }
            try {
                $this->applyStatus($order, $this->client->order($order));
            } catch (Throwable) {
                $order->update(['goorder_error' => 'goorder_sync_unavailable']);
            }
        });
    }

    public function applyStatus(Order $order, array $remote): void
    {
        if ((string) ($remote['id'] ?? '') !== $order->goorder_id) {
            throw new RuntimeException('goorder_identity_mismatch');
        }
        $status = $remote['status'] ?? '';
        $local = match ($status) {
            'WAITING_FOR_ACCEPTED' => 'waiting_staff',
            'ACCEPTED', 'CLOSED' => match ($remote['tracking_status'] ?? null) {
                'RECEIVED' => 'completed',
                'DELIVERY' => 'delivering',
                'READY' => 'ready',
                default => 'accepted',
            },
            'REJECTED' => 'rejected',
            'CANCELED' => 'canceled',
            // CONFIRMED alone does not mean staff accepted the order.
            'CONFIRMED', 'OPEN' => 'submission_uncertain',
            default => throw new RuntimeException('goorder_unknown_status'),
        };
        $changes = [
            'status' => $local, 'goorder_status' => $status,
            'goorder_synced_at' => now(), 'goorder_error' => null,
        ];
        if (in_array($status, ['ACCEPTED', 'CLOSED'], true) && ! empty($remote['estimated_preparation_at'])) {
            $changes['expected_ready_at'] = CarbonImmutable::parse($remote['estimated_preparation_at'], 'Europe/Warsaw')->utc();
        }
        $order->update($changes);
    }

    public function quote(array $remote): array
    {
        $total = $this->money($remote['total_money'] ?? null);
        $items = collect($remote['items'] ?? [])->map(fn ($item) => [
            'item_id' => $item['item_id'], 'name' => (string) ($item['name'] ?? ''),
            'quantity' => $item['quantity'], 'total' => $this->money($item['total_money'] ?? null),
        ])->values()->all();
        if (! $items) {
            throw new RuntimeException('goorder_empty_quote');
        }

        return ['total' => $total, 'delivery' => $this->money($remote['delivery_fee_money'] ?? ['amount' => 0, 'currency' => 'PLN']), 'items' => $items];
    }

    private function money(?array $money): int
    {
        if (($money['currency'] ?? null) !== 'PLN' || ! is_numeric($money['amount'] ?? null) || $money['amount'] < 0) {
            throw new RuntimeException('goorder_invalid_money');
        }

        return (int) round($money['amount'] * 100);
    }

    public function quoteHash(array $quote): string
    {
        return hash('sha256', json_encode($quote, JSON_THROW_ON_ERROR));
    }

    private function recordFailure(Order $order, Throwable $exception): void
    {
        $order->update([
            'status' => $order->goorder_submit_started_at ? 'submission_uncertain' : 'goorder_failed',
            'goorder_error' => $exception instanceof RuntimeException && str_starts_with($exception->getMessage(), 'goorder_')
                ? $exception->getMessage() : 'goorder_connection_error',
        ]);
    }
}

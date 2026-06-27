<?php

namespace App\Services\GoPos;

use App\Models\Customer;
use App\Models\Order;
use Carbon\CarbonImmutable;
use RuntimeException;
use Illuminate\Support\Str;

class GoPosOrderSender
{
    public function __construct(private readonly GoPosClient $client) {}

    public function send(Order $order): array
    {
        $order->loadMissing(['customer', 'items.menuItem']);
        $customerPayload = $this->syncCustomer($order);
        $organizationId = $this->client->organizationId();
        $goposOrderId = $order->gopos_id;
        $goposOrder = $order->gopos_payload['created'] ?? $order->gopos_payload ?? [];

        $existingPayload = is_array($order->gopos_payload) ? $order->gopos_payload : [];

        if (! $goposOrderId) {
            $payload = $this->orderPayload($order, $customerPayload['id'] ?? null);
            $response = $this->client->post("/api/v3/{$organizationId}/orders", $payload);
            $goposOrder = $response['data'] ?? $response;
            $goposOrderId = $goposOrder['id'] ?? null;
        } else {
            $payload = data_get($existingPayload, 'request', []);
        }

        if (! $goposOrderId) {
            throw new RuntimeException('GoPOS created order response does not contain order id.');
        }

        $sendResponse = null;

        if (($goposOrder['status'] ?? null) !== 'EXTERNAL') {
            $sendResponse = $this->client->post("/api/v3/{$organizationId}/orders/{$goposOrderId}/send", [
                'email' => $order->customer->email,
                'send_without_ereceipt' => true,
            ]);
        }

        $freshResponse = $this->client->get("/api/v3/{$organizationId}/orders/{$goposOrderId}");
        $freshOrder = $freshResponse['data'] ?? $freshResponse;
        $finalGoPosOrder = filled($freshOrder) ? $freshOrder : $goposOrder;

        $order->update([
            'status' => 'sent_to_gopos',
            'gopos_id' => $goposOrderId,
            'gopos_uid' => $finalGoPosOrder['uid'] ?? $goposOrder['uid'] ?? null,
            'gopos_number' => $finalGoPosOrder['number'] ?? $goposOrder['number'] ?? null,
            'gopos_payload' => array_filter(array_merge($existingPayload, [
                'request' => $payload,
                'created' => $goposOrder,
                'sent' => $sendResponse,
                'fresh' => $freshOrder,
            ])),
            'gopos_error' => null,
            'gopos_sent_at' => now(),
        ]);

        return $goposOrder;
    }

    private function syncCustomer(Order $order): array
    {
        $customer = $order->customer;
        $organizationId = $this->client->organizationId();
        $phone = $this->normalizePhone($customer->phone);

        $existing = $customer->gopos_id
            ? ($this->client->get("/api/v3/{$organizationId}/clients/{$customer->gopos_id}", ['include' => 'address,contact'])['data'] ?? null)
            : null;

        if (! $existing) {
            $clients = $this->client->list("/api/v3/{$organizationId}/clients", [
                'include' => 'address,contact',
                'status' => 'ENABLED',
            ], 200);

            $existing = collect($clients)->first(function (array $client) use ($phone): bool {
                return $this->normalizePhone(data_get($client, 'contact.phone_number')) === $phone;
            });
        }

        $payload = $this->customerPayload($order);

        if ($existing) {
            $customer->update([
                'gopos_id' => $existing['id'] ?? null,
                'gopos_payload' => $existing,
                'gopos_synced_at' => now(),
            ]);

            return $existing;
        }

        $response = $this->client->post("/api/v3/{$organizationId}/clients?include=address,contact", $payload);
        $goposCustomer = $response['data'] ?? $response;

        $customer->update([
            'gopos_id' => $goposCustomer['id'] ?? null,
            'gopos_payload' => $goposCustomer,
            'gopos_synced_at' => now(),
        ]);

        return $goposCustomer;
    }

    private function customerPayload(Order $order): array
    {
        $customer = $order->customer;

        return array_filter([
            'name' => $customer->name,
            'contact_phone_number' => $this->normalizePhone($customer->phone),
            'contact_email' => $customer->email,
            'tax_id_no' => $order->nip ?: $customer->nip,
            'address_street' => $order->street ?: $customer->street,
            'address_build_nr' => $order->building_number ?: $customer->building_number,
            'address_flat_nr' => $order->apartment_number ?: $customer->apartment_number,
            'address_city' => $order->city ?: $customer->city ?: 'Toruń',
            'address_country' => 'Polska',
        ], fn ($value) => filled($value));
    }

    private function orderPayload(Order $order, ?int $goposCustomerId): array
    {
        $items = $order->items->map(function ($item): array {
            $payload = [
                'quantity' => (float) $item->quantity,
                'item_id' => (int) $item->gopos_id,
                'unit_price' => [
                    'amount' => (float) $item->unit_price,
                    'currency' => 'PLN',
                ],
            ];

            $taxId = data_get($item->payload, 'gopos_tax_id');
            if ($taxId) {
                $payload['tax'] = ['id' => (int) $taxId];
            }

            return $payload;
        })->values()->all();

        if ($order->delivery_type === 'delivery' && (float) $order->delivery_cost > 0) {
            $deliveryItem = [
                'quantity' => 1,
                'item_id' => 214,
                'name' => 'Dostawa',
                'unit_price' => [
                    'amount' => (float) $order->delivery_cost,
                    'currency' => 'PLN',
                ],
                'comment' => 'Koszt dostawy ze strony www',
                'tax' => ['id' => 2],
            ];

            $items[] = $deliveryItem;
        }

        $payload = [
            'type' => $order->delivery_type === 'delivery' ? 'DELIVERY' : 'PICK_UP',
            'status' => 'EXTERNAL',
            'source' => 'UMAMI_WWW',
            'source_number' => $order->number,
            'reference_id' => $order->number,
            'comment' => $this->orderComment($order),
            'items' => $items,
            'transactions' => [$this->transactionPayload($order)],
            'contact' => [
                'name' => $order->customer->name,
                'phone_number' => $this->normalizePhone($order->customer->phone),
                'email' => $order->customer->email ?: null,
                'reference_id' => $goposCustomerId ? (string) $goposCustomerId : null,
                'source' => 'UMAMI_WWW',
            ],
            'fiscalization' => [
                'status' => 'NOT_FISCALIZED',
                'type' => 'NONE',
                'email' => $order->customer->email ?: '',
            ],
            'custom_fields' => [
                'payment_type' => $order->payment_type,
                'wants_invoice' => $order->wants_invoice ? 'yes' : 'no',
                'delivery_cost' => (string) $order->delivery_cost,
            ],
        ];

        if ($order->scheduled_at) {
            $scheduledAt = $this->goPosDateTime($order);
            $payload['estimated_delivery_at'] = $scheduledAt;
            $payload['execution_at'] = $scheduledAt;
            $payload['custom_fields']['requested_for'] = $scheduledAt;
        }

        if ($order->delivery_type === 'delivery') {
            $deliveryQuote = data_get($order->gopos_payload, 'delivery_quote', []);
            $deliveryZone = data_get($deliveryQuote, 'zone', []);
            $coordinates = data_get($deliveryQuote, 'coordinates', []);

            $payload['delivery'] = [
                'delivery_zone' => [
                    'id' => (string) data_get($deliveryZone, 'id'),
                    'name' => data_get($deliveryZone, 'name'),
                ],
                'address' => [
                    'street' => $order->street,
                    'build_nr' => $order->building_number,
                    'flat_nr' => $order->apartment_number,
                    'city' => $order->city ?: 'Toruń',
                    'country' => 'Polska',
                ],
                'coordinates' => [
                    'latitude' => data_get($coordinates, 'lat'),
                    'longitude' => data_get($coordinates, 'lng'),
                ],
                'comment' => $this->deliveryComment($deliveryQuote),
            ];
        }

        return $this->filterRecursive($payload);
    }

    private function transactionPayload(Order $order): array
    {
        return [
            'payment_method_id' => $order->payment_type === 'card' ? 2 : 1,
            'paid' => false,
            'price' => [
                'amount' => (float) $order->total,
                'currency' => 'PLN',
            ],
            'tender_price' => [
                'amount' => (float) $order->total,
                'currency' => 'PLN',
            ],
            'reference_id' => $order->number,
        ];
    }

    private function orderComment(Order $order): string
    {
        return Str::limit(implode(' | ', array_filter([
            $order->comment,
            $order->scheduled_at ? 'Termin: '.$this->displayScheduledAt($order) : null,
            $order->payment_type === 'cash' ? 'Płatność: gotówka' : 'Płatność: karta',
            $order->wants_invoice ? 'Faktura NIP: '.$order->nip : null,
        ])), 255, '');
    }

    private function goPosDateTime(Order $order): string
    {
        return $this->scheduledAtInWarsaw($order)->format('Y-m-d\TH:i:sP');
    }

    private function displayScheduledAt(Order $order): string
    {
        return $this->scheduledAtInWarsaw($order)->format('d.m.Y H:i');
    }

    private function scheduledAtInWarsaw(Order $order): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $order->scheduled_at->format('Y-m-d H:i:s'),
            'Europe/Warsaw',
        );
    }

    private function deliveryComment(array $deliveryQuote): string
    {
        $parts = [];
        $zoneName = data_get($deliveryQuote, 'zone.name');
        $zoneId = data_get($deliveryQuote, 'zone.id');
        $distance = data_get($deliveryQuote, 'distance_km');
        $lat = data_get($deliveryQuote, 'coordinates.lat');
        $lng = data_get($deliveryQuote, 'coordinates.lng');

        if ($zoneName) {
            $parts[] = trim($zoneName.' '.($zoneId ? '(ID '.$zoneId.')' : ''));
        }

        if ($distance !== null) {
            $parts[] = 'odległość '.$distance.' km';
        }

        if ($lat !== null && $lng !== null) {
            $parts[] = 'GPS '.$lat.', '.$lng;
        }

        return Str::limit(implode(' | ', $parts), 255, '');
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return str_starts_with($digits, '48') ? $digits : '48'.$digits;
    }

    private function filterRecursive(array $payload): array
    {
        return collect($payload)
            ->map(fn ($value) => is_array($value) ? $this->filterRecursive($value) : $value)
            ->filter(fn ($value) => ! ($value === null || $value === '' || $value === []))
            ->all();
    }
}

<?php

namespace App\Services\GoOrder;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoOrderClient
{
    public function request(string $method, string $path, array $data = [], ?Order $order = null): array
    {
        // Never retry writes: the storefront does not promise idempotency.
        $response = Http::baseUrl(config('goorder.base_url').'/api')
            ->acceptJson()->asJson()->connectTimeout(5)->timeout(config('goorder.timeout'))
            ->withoutRedirecting()
            ->withHeaders($order ? ['order-token' => $order->goorder_token] : [])
            ->send($method, $path, [$method === 'GET' ? 'query' : 'json' => $data]);

        if (! $response->successful()) {
            // Do not store/log an upstream body containing contact details or order tokens.
            throw new RuntimeException('goorder_http_'.$response->status());
        }

        $result = $response->json('data');
        if (! is_array($result)) {
            throw new RuntimeException('goorder_invalid_response');
        }

        return $result;
    }

    public function menu(): array
    {
        return $this->request('GET', 'config/menus');
    }

    public function order(Order $order): array
    {
        return $this->request('GET', 'orders/'.rawurlencode($order->goorder_id), [
            'include' => 'all,tracking', 'checkStatus' => '1',
        ], $order);
    }
}

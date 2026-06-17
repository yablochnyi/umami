<?php

namespace App\Services\GoPos;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoPosClient
{
    public function organizationId(): string
    {
        $organizationId = config('gopos.organization_id');

        if (blank($organizationId)) {
            throw new RuntimeException('Missing GOPOS_ORGANIZATION_ID in .env.');
        }

        return (string) $organizationId;
    }

    public function get(string $path, array $query = []): array
    {
        $response = $this->sendWithFreshToken('get', $path, $query);

        $response->throw();

        return $response->json() ?? [];
    }

    public function post(string $path, array $payload = []): array
    {
        $response = $this->sendWithFreshToken('post', $path, $payload);

        $response->throw();

        return $response->json() ?? [];
    }

    private function sendWithFreshToken(string $method, string $path, array $data): \Illuminate\Http\Client\Response
    {
        $response = $this->http()
            ->acceptJson()
            ->withToken($this->token())
            ->{$method}($this->url($path), $data);

        if ($response->status() === 401) {
            Cache::forget(config('gopos.token_cache_key'));

            $response = $this->http()
                ->acceptJson()
                ->withToken($this->token())
                ->{$method}($this->url($path), $data);
        }

        return $response;
    }

    public function list(string $path, array $query = [], int $size = 100): array
    {
        $items = [];
        $page = 0;
        $maxPages = 100;

        do {
            $payload = $this->get($path, array_merge($query, [
                'page' => $page,
                'size' => $size,
            ]));

            $pageItems = $payload['data'] ?? [];
            $items = array_merge($items, $pageItems);

            $hasNextPage = $payload['next_page'] ?? (count($pageItems) >= $size);
            $page++;
        } while ($hasNextPage && $page < $maxPages);

        return $items;
    }

    public function menuExportData(int $size = 100): array
    {
        $organizationId = $this->organizationId();

        return [
            'exported_at' => now()->toIso8601String(),
            'organization_id' => $organizationId,
            'endpoints' => [
                'organization' => "/api/v3/{$organizationId}",
                'menus' => "/api/v3/{$organizationId}/menus",
                'categories' => "/api/v3/{$organizationId}/categories",
                'items' => "/api/v3/{$organizationId}/items",
                'price_lists' => "/api/v3/{$organizationId}/price_lists",
                'modifier_groups' => "/api/v3/{$organizationId}/modifier_groups",
                'taxes' => "/api/v3/{$organizationId}/taxes",
                'payment_methods' => "/api/v3/{$organizationId}/payment_methods",
                'points_of_sale' => "/api/v3/{$organizationId}/points_of_sale",
                'directions' => "/api/v3/{$organizationId}/directions",
            ],
            'data' => [
                'organization' => $this->get("/api/v3/{$organizationId}")['data'] ?? null,
                'menus' => $this->list("/api/v3/{$organizationId}/menus", ['include' => 'pages,pages.items,pages.translations'], $size),
                'categories' => $this->list("/api/v3/{$organizationId}/categories", ['include' => 'translations,direction,points_of_sale'], $size),
                'items' => $this->list("/api/v3/{$organizationId}/items", ['include' => 'category,tax,translations,modifier_groups,price_overrides,order_types'], $size),
                'price_lists' => $this->list("/api/v3/{$organizationId}/price_lists", ['include' => 'items,predicates,predicates.conditions'], $size),
                'modifier_groups' => $this->list("/api/v3/{$organizationId}/modifier_groups", ['include' => 'options,translations,quantity_info_overrides'], $size),
                'taxes' => $this->list("/api/v3/{$organizationId}/taxes", [], $size),
                'payment_methods' => $this->list("/api/v3/{$organizationId}/payment_methods", [], $size),
                'points_of_sale' => $this->list("/api/v3/{$organizationId}/points_of_sale", ['include' => 'direction'], $size),
                'directions' => $this->list("/api/v3/{$organizationId}/directions", [], $size),
            ],
        ];
    }

    public function clientsExportData(int $size = 100): array
    {
        $organizationId = $this->organizationId();

        return [
            'exported_at' => now()->toIso8601String(),
            'organization_id' => $organizationId,
            'endpoints' => [
                'clients' => "/api/v3/{$organizationId}/clients",
                'client_groups' => "/api/v3/{$organizationId}/clients/groups",
            ],
            'data' => [
                'clients' => $this->list("/api/v3/{$organizationId}/clients", [
                    'include' => 'client_group,id_card,address,contact',
                ], $size),
                'client_groups' => $this->list("/api/v3/{$organizationId}/clients/groups", [], $size),
            ],
        ];
    }

    public function token(): string
    {
        $cacheKey = config('gopos.token_cache_key');

        return Cache::remember($cacheKey, now()->addMinutes(50), function (): string {
            $clientId = config('gopos.client_id');
            $clientSecret = config('gopos.client_secret');

            if (blank($clientId) || blank($clientSecret)) {
                throw new RuntimeException('Missing GOPOS_CLIENT_ID or GOPOS_CLIENT_SECRET in .env.');
            }

            $response = $this->http()
                ->asForm()
                ->post($this->url('/oauth/token'), [
                    'grant_type' => 'organization',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'organization_id' => $this->organizationId(),
                ])
                ->throw()
                ->json();

            $token = $response['access_token'] ?? null;

            if (blank($token)) {
                throw new RuntimeException('GoPOS OAuth response does not contain access_token.');
            }

            return $token;
        });
    }

    private function http(): PendingRequest
    {
        return Http::timeout(config('gopos.timeout'));
    }

    private function url(string $path): string
    {
        return config('gopos.base_url').'/'.ltrim($path, '/');
    }
}

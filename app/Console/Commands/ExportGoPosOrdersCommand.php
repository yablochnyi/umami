<?php

namespace App\Console\Commands;

use App\Services\GoPos\GoPosClient;
use Illuminate\Console\Command;

class ExportGoPosOrdersCommand extends Command
{
    protected $signature = 'gopos:export-orders {--path=storage/app/gopos/orders-export.json} {--limit=50}';

    protected $description = 'Export latest GoPOS orders to JSON';

    public function handle(GoPosClient $goPos): int
    {
        try {
            $organizationId = $goPos->organizationId();
            $limit = (int) $this->option('limit');
            $pathOption = (string) $this->option('path');
            $path = str_starts_with($pathOption, '/') ? $pathOption : base_path($pathOption);

            if ($limit < 1 || $limit > 200) {
                $this->error('The --limit option must be between 1 and 200.');

                return self::FAILURE;
            }

            $this->info("Fetching latest {$limit} GoPOS orders for organization {$organizationId}...");

            $payload = $goPos->get("/api/v3/{$organizationId}/orders", [
                'page' => 0,
                'size' => $limit,
                'sort' => 'created_at,desc',
                'include' => implode(',', [
                    'contact',
                    'delivery',
                    'delivery_price',
                    'custom_fields',
                    'fiscalization',
                    'items',
                    'items.tax',
                    'items.product',
                    'transactions',
                    'tip',
                ]),
            ]);

            $orders = $payload['data'] ?? [];
            $export = [
                'exported_at' => now()->toIso8601String(),
                'organization_id' => $organizationId,
                'endpoint' => "/api/v3/{$organizationId}/orders",
                'query' => [
                    'page' => 0,
                    'size' => $limit,
                    'sort' => 'created_at,desc',
                ],
                'count' => count($orders),
                'raw' => $payload,
                'orders' => $orders,
            ];

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->info("GoPOS orders export saved: {$path}");
            $this->table(['Metric', 'Value'], [
                ['orders', (string) count($orders)],
                ['path', $path],
            ]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}

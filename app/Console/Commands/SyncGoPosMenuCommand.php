<?php

namespace App\Console\Commands;

use App\Services\GoPos\GoPosClient;
use App\Services\GoPos\GoPosMenuSynchronizer;
use Illuminate\Console\Command;

class SyncGoPosMenuCommand extends Command
{
    protected $signature = 'gopos:sync-menu {--from-json=} {--dry-run} {--refresh-images} {--size=100}';

    protected $description = 'Synchronize local menu categories, items and images from GoPOS';

    public function handle(GoPosClient $goPos, GoPosMenuSynchronizer $synchronizer): int
    {
        try {
            $size = (int) $this->option('size');

            if ($size < 1 || $size > 100) {
                $this->error('The --size option must be between 1 and 100.');

                return self::FAILURE;
            }

            if ($fromJson = $this->option('from-json')) {
                $path = str_starts_with($fromJson, '/') ? $fromJson : base_path($fromJson);
                if (! is_file($path)) {
                    $this->error("JSON file not found: {$path}");

                    return self::FAILURE;
                }

                $this->info("Loading GoPOS menu data from {$path}...");
                $export = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            } else {
                $this->info("Fetching fresh GoPOS menu data for organization {$goPos->organizationId()}...");
                $export = $goPos->menuExportData($size);
            }

            $stats = $synchronizer->sync(
                export: $export,
                dryRun: (bool) $this->option('dry-run'),
                refreshImages: (bool) $this->option('refresh-images'),
            );

            $this->table(['Metric', 'Count'], collect($stats)
                ->map(fn ($value, $key) => [$key, $value])
                ->values()
                ->all());

            $this->info($this->option('dry-run') ? 'Dry run finished. Database was not changed.' : 'GoPOS menu synchronized.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}

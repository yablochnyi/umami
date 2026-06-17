<?php

namespace App\Console\Commands;

use App\Services\GoPos\GoPosClient;
use Illuminate\Console\Command;

class ExportGoPosClientsCommand extends Command
{
    protected $signature = 'gopos:export-clients {--path=storage/app/gopos/clients-export.json} {--size=100}';

    protected $description = 'Export GoPOS clients and client groups to JSON';

    public function handle(GoPosClient $goPos): int
    {
        try {
            $organizationId = $goPos->organizationId();
            $size = (int) $this->option('size');
            $pathOption = $this->option('path');
            $path = str_starts_with($pathOption, '/') ? $pathOption : base_path($pathOption);

            if ($size < 1 || $size > 200) {
                $this->error('The --size option must be between 1 and 200.');

                return self::FAILURE;
            }

            $this->info("Fetching GoPOS clients for organization {$organizationId}...");
            $export = $goPos->clientsExportData($size);

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $this->info("GoPOS clients export saved: {$path}");
            $this->table(['Resource', 'Count'], collect($export['data'])
                ->map(fn ($value, $key) => [$key, is_array($value) ? count($value) : ($value ? 1 : 0)])
                ->values()
                ->all());

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}

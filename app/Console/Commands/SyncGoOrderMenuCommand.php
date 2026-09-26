<?php

namespace App\Console\Commands;

use App\Services\GoOrder\GoOrderClient;
use App\Services\GoOrder\GoOrderMenuSynchronizer;
use App\Services\GoPos\GoPosClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncGoOrderMenuCommand extends Command
{
    protected $signature = 'goorder:sync-menu {--dry-run} {--allow-large-removal : Allow a reviewed removal of more than 25% of items}';

    protected $description = 'Import new storefront dishes; preserve existing content; sync publication and availability';

    public function handle(GoOrderClient $client, GoOrderMenuSynchronizer $synchronizer, GoPosClient $pos): int
    {
        $lock = Cache::lock('goorder:menu-sync', 1800);
        if (! $lock->get()) {
            $this->error('Another menu synchronization is running.');

            return self::FAILURE;
        }
        try {
            $data = $client->menu();
            // GoPOS is used only to route new products through the existing checkout.
            $needsPosRouting = ! config('goorder.enabled') && config('gopos.organization_id');
            $posItems = $needsPosRouting ? $pos->list('/api/v3/'.$pos->organizationId().'/items', ['include' => 'tax,category,modifier_groups']) : [];
            $posGroups = $needsPosRouting ? $pos->list('/api/v3/'.$pos->organizationId().'/modifier_groups', ['include' => 'options']) : [];
            $stats = $synchronizer->sync($data, $posItems, (bool) $this->option('dry-run'), (bool) $this->option('allow-large-removal'), $posGroups);
            $this->table(['Metric', 'Count'], collect($stats)->map(fn ($count, $name) => [$name, $count])->values()->all());
            $this->info($this->option('dry-run') ? 'Dry run: no menu changes saved.' : 'GoOrder storefront synchronized. Existing content and prices preserved.');
            Log::info('GoOrder menu sync completed', ['dry_run' => (bool) $this->option('dry-run'), 'stats' => $stats]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());
            Log::error('GoOrder menu sync failed', ['exception' => get_class($exception)]);

            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}

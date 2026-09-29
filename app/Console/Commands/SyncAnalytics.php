<?php

namespace App\Console\Commands;

use App\Services\GoPos\GoPosAnalyticsSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncAnalytics extends Command
{
    protected $signature = 'analytics:sync {--from= : First local calendar date, YYYY-MM-DD} {--requested : Run only when requested by an administrator}';

    protected $description = 'Refresh the private GoPOS sales archive without sending or changing orders';

    public function handle(GoPosAnalyticsSync $sync): int
    {
        if ($this->option('requested') && ! Cache::has('analytics.sync.requested')) {
            return self::SUCCESS;
        }
        $lock = Cache::lock('analytics.sync.lock', 7200);
        if (! $lock->get()) {
            $this->info('An analytics sync is already running.');

            return self::SUCCESS;
        }
        try {
            Cache::forget('analytics.sync.requested');
            $run = $sync->sync($this->option('from') ?: config('analytics.from'));
            $this->info("Analytics sync #{$run->id}: {$run->orders_count} orders refreshed.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Analytics sync failed: '.class_basename($e).'. Previous data is preserved; retry is safe.');

            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}

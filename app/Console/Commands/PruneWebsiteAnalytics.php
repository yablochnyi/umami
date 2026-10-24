<?php

namespace App\Console\Commands;

use App\Models\WebsiteEvent;
use App\Models\WebsiteVisit;
use Illuminate\Console\Command;

class PruneWebsiteAnalytics extends Command
{
    protected $signature = 'website-analytics:prune';

    protected $description = 'Delete website analytics beyond the retention period';

    public function handle(): int
    {
        $cutoff = now('Europe/Warsaw')->subDays(config('website_analytics.retention_days'))->toDateString();
        do {
            $ids = WebsiteEvent::where('business_date', '<', $cutoff)->limit(1000)->pluck('id');
            WebsiteEvent::whereIn('id', $ids)->delete();
        } while ($ids->count() === 1000);
        WebsiteVisit::where('last_seen_at', '<', now('UTC')->subDays(config('website_analytics.retention_days') + 1))
            ->whereNotIn('id', WebsiteEvent::select('website_visit_id'))->delete();
        $this->info('Expired website analytics removed.');

        return self::SUCCESS;
    }
}

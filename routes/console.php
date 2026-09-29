<?php

use App\Support\UmamiSitemapFactory;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('analytics:sync')->dailyAt(config('analytics.time', '03:40'))->timezone('Europe/Warsaw')
    ->when(fn () => config('analytics.enabled', true))->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/analytics-sync.log'));
Schedule::command('analytics:sync --requested')->everyMinute()->withoutOverlapping(120)
    ->appendOutputTo(storage_path('logs/analytics-sync.log'));

Schedule::command('goorder:sync-status')->everyMinute()->withoutOverlapping();
Schedule::command('goorder:sync-menu')
    ->dailyAt(config('goorder.menu_sync_time'))
    ->timezone('Europe/Warsaw')
    ->when(fn () => config('goorder.menu_sync_enabled'))
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/goorder-menu-sync.log'));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:generate', function () {
    $path = public_path('sitemap.xml');

    file_put_contents($path, app(UmamiSitemapFactory::class)->build()->render());

    $this->info("Sitemap generated: {$path}");
})->purpose('Generate the public sitemap.xml file');

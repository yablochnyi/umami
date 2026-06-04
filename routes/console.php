<?php

use App\Support\UmamiSitemapFactory;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:generate', function () {
    $path = public_path('sitemap.xml');

    file_put_contents($path, app(UmamiSitemapFactory::class)->build()->render());

    $this->info("Sitemap generated: {$path}");
})->purpose('Generate the public sitemap.xml file');

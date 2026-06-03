<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\SitemapController;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', function () {
    $siteUrl = rtrim(
        SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://www.umamisushifood.pl',
        '/'
    );

    return response("User-agent: *\nAllow: /\nSitemap: {$siteUrl}/sitemap.xml\n", 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');
Route::get('/{slug}', LegalPageController::class)
    ->whereIn('slug', ['polityka-prywatnosci', 'polityka-plikow-cookie', 'regulamin'])
    ->name('legal');
Route::get('/{locale}/{slug}', LegalPageController::class)
    ->whereIn('locale', ['uk', 'en'])
    ->whereIn('slug', ['polityka-konfidentsiynosti', 'polityka-cookie', 'pravila-korystuvannya', 'privacy-policy', 'cookie-policy', 'terms'])
    ->name('legal.localized');
Route::get('/', HomeController::class)->name('home');
Route::get('/{locale}', HomeController::class)
    ->whereIn('locale', ['uk', 'en'])
    ->name('home.localized');

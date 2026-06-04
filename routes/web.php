<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\MenuPageController;
use App\Models\SiteSetting;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/robots.txt', function () {
    $siteUrl = rtrim(
        SiteSetting::query()->where('key', 'site_url')->value('value') ?: 'https://umamisushifood.pl',
        '/'
    );

    return response("User-agent: *\nAllow: /\nSitemap: {$siteUrl}/sitemap.xml\n", 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
        ValidateCsrfToken::class,
        VerifyCsrfToken::class,
    ])
    ->name('robots');
Route::get('/menu/{categorySlug}', [MenuPageController::class, 'category'])
    ->name('menu.category');
Route::get('/menu/{categorySlug}/{itemSlug}', [MenuPageController::class, 'item'])
    ->name('menu.item');
Route::get('/{locale}/menu/{categorySlug}', [MenuPageController::class, 'category'])
    ->whereIn('locale', ['uk', 'en'])
    ->name('menu.category.localized');
Route::get('/{locale}/menu/{categorySlug}/{itemSlug}', [MenuPageController::class, 'item'])
    ->whereIn('locale', ['uk', 'en'])
    ->name('menu.item.localized');
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

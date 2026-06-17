<?php

namespace App\Providers;

use App\View\Composers\SiteLayoutComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer([
            'layouts.site',
            'partials.site-header',
            'partials.site-footer',
            'welcome',
            'menu-category',
            'menu-item',
            'legal',
            'checkout',
        ], SiteLayoutComposer::class);
    }
}

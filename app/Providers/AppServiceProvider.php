<?php

namespace App\Providers;

use App\Models\Role;
use App\Models\User;
use App\Policies\AccessPolicy;
use App\Policies\AdminResourcePolicy;
use App\Support\AdminPermissions;
use App\View\Composers\SiteLayoutComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        foreach (AdminPermissions::RESOURCES as $key => $model) {
            $policy = 'admin.policy.'.$key;
            $this->app->bind($policy, fn () => new AdminResourcePolicy($key));
            Gate::policy($model, $policy);
        }
        Gate::policy(Role::class, AccessPolicy::class);
        Gate::policy(User::class, AccessPolicy::class);

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

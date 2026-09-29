<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\SiteSettings;
use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\AuditAdminActivity;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->brandName('UMAMI')
            ->brandLogo(fn () => view('filament.partials.brand'))
            ->favicon(asset('storage/umami/logo.jpg'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->font('Inter', provider: LocalFontProvider::class)
            ->darkMode(false)
            ->maxContentWidth(Width::Full)
            ->databaseTransactions()
            ->navigationGroups(array_map(
                fn (string $group) => NavigationGroup::make()->label(fn () => __('admin.groups.'.$group)),
                ['operations', 'menu', 'content', 'management'],
            ))
            ->renderHook(PanelsRenderHook::TOPBAR_END, fn () => view('filament.partials.language'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn () => view('filament.partials.language'))
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn () => view('filament.partials.audit-notice'))
            ->userMenuItems([
                Action::make('website')->label(fn () => __('admin.visit_site'))->url('/')->openUrlInNewTab()->icon('heroicon-o-arrow-top-right-on-square'),
            ])
            ->colors([
                'primary' => Color::generateV3Palette('#b82421'),
                'gray' => Color::Zinc,
                'success' => Color::Emerald,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                SiteSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([AdminLocale::class, AuditAdminActivity::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ], isPersistent: true);
    }
}

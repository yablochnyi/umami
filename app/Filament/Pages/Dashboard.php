<?php

namespace App\Filament\Pages;

use App\Models\MenuItem;
use App\Models\Order;
use Filament\Actions\Action;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected string $view = 'filament.pages.dashboard';

    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard');
    }

    public function getTitle(): string
    {
        return __('admin.dashboard');
    }

    public function getSubheading(): ?string
    {
        return now('Europe/Warsaw')->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY');
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('website')->label(__('admin.visit_site'))->url('/')->openUrlInNewTab()->icon('heroicon-o-arrow-top-right-on-square')->color('gray')];
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $orders = $user->hasAdminPermission('orders.view');
        $menu = $user->hasAdminPermission('menu_items.view');
        $today = now('Europe/Warsaw')->startOfDay();
        $query = Order::query()->whereBetween('created_at', [$today->copy()->utc(), $today->copy()->addDay()->utc()])
            ->whereNotIn('status', ['canceled', 'rejected', 'goorder_failed', 'gopos_error']);

        return [
            'canSeeOrders' => $orders, 'canSeeMenu' => $menu,
            'ordersToday' => $orders ? (clone $query)->count() : null,
            'totalToday' => $orders ? (clone $query)->sum('total') : null,
            'latestOrders' => $orders ? Order::query()->latest()->limit(7)->get() : collect(),
            'activeDishes' => $menu ? MenuItem::query()->visible()->count() : null,
            'publishedDishes' => $menu ? MenuItem::query()->where('goorder_published', true)->count() : null,
            'dishes' => $menu ? MenuItem::query()->with('category')->visible()->latest('id')->limit(4)->get() : collect(),
        ];
    }
}

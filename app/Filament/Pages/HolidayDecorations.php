<?php

namespace App\Filament\Pages;

use App\Support\HolidayDecorations as Decorations;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class HolidayDecorations extends Page
{
    protected static ?string $slug = 'holiday-decorations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.holiday-decorations';

    public static function getNavigationLabel(): string
    {
        return __('holidays.title');
    }

    public function getTitle(): string
    {
        return __('holidays.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.management');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAdminPermission('site_settings.view') ?? false;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    protected function getViewData(): array
    {
        $holidays = [];
        foreach (Decorations::CATALOG as $slug => $holiday) {
            $holidays[$slug] = [...$holiday, 'enabled' => $holiday['handler']::enabled()];
        }

        return ['holidays' => $holidays];
    }

    public function setHoliday(string $slug, bool $enabled): void
    {
        abort_unless(static::canAccess() && auth()->user()?->hasAdminPermission('site_settings.update'), 403);
        abort_unless(isset(Decorations::CATALOG[$slug]), 422);
        Decorations::CATALOG[$slug]['handler']::setEnabled($enabled);
        Notification::make()->success()->title(__('holidays.saved'))->send();
    }
}

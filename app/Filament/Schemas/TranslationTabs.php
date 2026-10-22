<?php

namespace App\Filament\Schemas;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

final class TranslationTabs
{
    public static function make(string $key, Closure $fields): Tabs
    {
        $tabs = [];
        foreach (['pl' => 'Polski', 'uk' => 'Українська', 'en' => 'English'] as $locale => $label) {
            $tabs[] = Tab::make($label)->id($key.'-'.$locale)->key($key.'-'.$locale)->columns(1)->schema($fields($locale));
        }

        return Tabs::make($key)
            ->contained(false)
            ->columnSpanFull()
            ->activeTab(fn () => match (app()->getLocale()) {
                'uk' => 2,
                'en' => 3,
                default => 1,
            })
            ->tabs($tabs);
    }
}

<?php

namespace App\Support;

use App\Models\SiteSetting;

final class WomensDayDecor
{
    public const KEY = 'womens_day_enabled';

    public static function enabled(): bool
    {
        return SiteSetting::where('key', self::KEY)->value('value') === '1';
    }

    public static function setEnabled(bool $enabled): void
    {
        $setting = SiteSetting::firstOrCreate(['key' => self::KEY], [
            'group' => 'appearance', 'label' => "Women's Day", 'type' => 'boolean', 'sort_order' => 102,
        ]);
        $setting->update(['value' => $enabled ? '1' : '0']);
    }
}

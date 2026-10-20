<?php

namespace App\Support;

use App\Models\SiteSetting;

final class NewYearDecor
{
    public const KEY = 'newyear_enabled';

    public static function enabled(): bool
    {
        return SiteSetting::where('key', self::KEY)->value('value') === '1';
    }

    public static function setEnabled(bool $enabled): void
    {
        $setting = SiteSetting::firstOrCreate(['key' => self::KEY], [
            'group' => 'appearance', 'label' => 'New Year', 'type' => 'boolean', 'sort_order' => 101,
        ]);
        $setting->update(['value' => $enabled ? '1' : '0']);
    }
}

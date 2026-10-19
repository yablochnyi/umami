<?php

namespace App\Support;

final class HolidayDecorations
{
    // Only implemented decorations belong here; never accept arbitrary setting keys.
    public const CATALOG = [
        'halloween' => [
            'setting' => HalloweenDecor::KEY,
            'handler' => HalloweenDecor::class,
            'preview' => 'assets/umami/halloween/pumpkins.webp',
        ],
    ];

    public static function settingLabel(string $key): ?string
    {
        foreach (self::CATALOG as $slug => $holiday) {
            if ($holiday['setting'] === $key) {
                return __('holidays.names.'.$slug);
            }
        }

        return null;
    }
}

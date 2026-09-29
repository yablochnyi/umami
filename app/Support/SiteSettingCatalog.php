<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;

final class SiteSettingCatalog
{
    public const FIELDS = [
        'logo_image' => ['image', 'branding'],
        'background_desktop' => ['image', 'branding'],
        'background_mobile' => ['image', 'branding'],
        'hero_video_desktop' => ['video', 'hero'],
        'hero_video_mobile' => ['video', 'hero'],
        'hero_poster' => ['image', 'hero'],
        'about_image' => ['image', 'about'],
        'phone' => ['phone', 'contact'],
        'phone_href' => ['call', 'contact'],
        'address' => ['text', 'contact'],
        'order_url' => ['url', 'contact'],
        'map_embed_url' => ['url', 'contact'],
        'site_url' => ['url', 'seo'],
        'google_analytics_id' => ['analytics', 'seo'],
    ];

    public static function label(SiteSetting $setting): string
    {
        return __('settings.labels.'.$setting->key);
    }

    public static function type(SiteSetting $setting): string
    {
        return self::FIELDS[$setting->key][0];
    }

    public static function group(SiteSetting $setting): string
    {
        return __('settings.groups.'.self::FIELDS[$setting->key][1]);
    }

    public static function uploadLimit(string $type): int
    {
        // Respect PHP and Livewire's default temporary upload limit as well as the field limit.
        return (int) min($type === 'video' ? 12288 : 5120, UploadedFile::getMaxFilesize() / 1024);
    }
}

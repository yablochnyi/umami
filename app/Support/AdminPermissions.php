<?php

namespace App\Support;

use App\Models;

final class AdminPermissions
{
    public const RESOURCES = [
        'orders' => Models\Order::class,
        'customers' => Models\Customer::class,
        'menu_items' => Models\MenuItem::class,
        'menu_categories' => Models\MenuCategory::class,
        'gallery_images' => Models\GalleryImage::class,
        'site_texts' => Models\SiteText::class,
        'social_links' => Models\SocialLink::class,
        'site_settings' => Models\SiteSetting::class,
    ];

    public static function actions(string $resource): array
    {
        return match ($resource) {
            'orders', 'customers', 'site_settings', 'site_texts' => ['view', 'update'],
            default => ['view', 'create', 'update', 'delete'],
        };
    }

    public static function keys(): array
    {
        $keys = [];
        foreach (array_keys(self::RESOURCES) as $resource) {
            foreach (self::actions($resource) as $action) {
                $keys[] = "$resource.$action";
            }
        }

        return $keys;
    }

    public static function resource(string $model): string
    {
        return array_search($model, self::RESOURCES, true) ?: match ($model) {
            Models\Role::class => 'roles',
            Models\User::class => 'users',
            default => throw new \InvalidArgumentException('Unknown admin resource'),
        };
    }
}

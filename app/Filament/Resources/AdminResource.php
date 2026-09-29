<?php

namespace App\Filament\Resources;

use App\Support\AdminPermissions;
use Filament\Resources\Resource;

abstract class AdminResource extends Resource
{
    protected static bool $shouldCheckPolicyExistence = false;

    protected static bool $hasTitleCaseModelLabel = false;

    public static function permissionKey(): string
    {
        return AdminPermissions::resource(static::getModel());
    }

    public static function getModelLabel(): string
    {
        return __('admin.singular.'.static::permissionKey());
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.'.static::permissionKey());
    }

    public static function getNavigationLabel(): string
    {
        return static::getPluralModelLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.groups.'.match (static::permissionKey()) {
            'orders', 'customers' => 'operations',
            'menu_items', 'menu_categories' => 'menu',
            'users', 'roles', 'site_settings' => 'management',
            default => 'content',
        });
    }
}

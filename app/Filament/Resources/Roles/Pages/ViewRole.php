<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\ViewRecord;

class ViewRole extends ViewRecord
{
    use HasPermissionGroups;

    protected static string $resource = RoleResource::class;
}

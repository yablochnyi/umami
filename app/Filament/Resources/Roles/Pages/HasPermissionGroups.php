<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Support\AdminPermissions;
use Illuminate\Validation\ValidationException;

trait HasPermissionGroups
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (array_keys(AdminPermissions::RESOURCES) as $resource) {
            $data['permission_groups'][$resource] = array_values(array_filter(AdminPermissions::actions($resource),
                fn ($action) => ($data['is_admin'] ?? false) || in_array("$resource.$action", $data['permissions'] ?? [], true)));
        }

        return $data;
    }

    protected function permissionsData(array $data): array
    {
        $permissions = [];
        foreach ($data['permission_groups'] ?? [] as $resource => $actions) {
            foreach ($actions as $action) {
                if (! in_array("$resource.$action", AdminPermissions::keys(), true)) {
                    throw ValidationException::withMessages(['data.permission_groups' => __('admin.invalid_permissions')]);
                }
                $permissions[] = "$resource.$action";
            }
        }

        return ['name' => $data['name'], 'permissions' => $permissions];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->permissionsData($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->permissionsData($data);
    }
}

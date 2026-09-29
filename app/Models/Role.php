<?php

namespace App\Models;

use App\Support\AdminPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Role extends Model
{
    protected $fillable = ['name', 'permissions'];

    protected function casts(): array
    {
        return ['permissions' => 'array', 'is_admin' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Role $role): void {
            if ($role->exists && ($role->isDirty('is_admin') || $role->getOriginal('is_admin'))) {
                throw ValidationException::withMessages(['name' => __('admin.protected_role')]);
            }
            $permissions = $role->permissions ?? [];
            if (array_diff($permissions, AdminPermissions::keys())) {
                throw ValidationException::withMessages(['permissions' => __('admin.invalid_permissions')]);
            }
            // A write permission never grants access without the corresponding view permission.
            foreach ($permissions as $permission) {
                $permissions[] = explode('.', $permission)[0].'.view';
            }
            $role->permissions = array_values(array_unique($permissions));
        });
        static::deleting(function (Role $role): void {
            if ($role->is_admin || $role->users()->exists()) {
                throw ValidationException::withMessages(['name' => __('admin.role_in_use')]);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->is_admin ? __('admin.administrator') : $this->name;
    }
}

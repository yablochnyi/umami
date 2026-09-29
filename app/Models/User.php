<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if ($user->isDirty('role_id')) {
                $user->protectLastAdministrator();
            }
        });
        static::deleting(fn (User $user) => $user->protectLastAdministrator());
    }

    private function protectLastAdministrator(): void
    {
        // Panel writes run in transactions. The role lock serializes simultaneous access changes.
        $admin = Role::query()->where('is_admin', true)->lockForUpdate()->first();
        if ($admin && (int) $this->getOriginal('role_id') === $admin->id
            && ! static::query()->where('role_id', $admin->id)->whereKeyNot($this->getKey())->exists()) {
            throw ValidationException::withMessages(['role_id' => __('admin.last_admin')]);
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && ($this->isAdministrator() || collect($this->role?->permissions ?? [])
            ->contains(fn (string $permission): bool => str_ends_with($permission, '.view')));
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdministrator(): bool
    {
        return (bool) $this->role?->is_admin;
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        $permissions = $this->role?->permissions ?? [];

        return in_array($permission, $permissions, true)
            && in_array(explode('.', $permission)[0].'.view', $permissions, true);
    }
}

<?php

namespace App\Policies;

use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Database\Eloquent\Model;

class AdminResourcePolicy
{
    public function __construct(private string $resource) {}

    public function viewAny(User $user): bool
    {
        return $user->hasAdminPermission($this->resource.'.view');
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'create');
    }

    public function update(User $user, Model $record): bool
    {
        return $this->allowed($user, 'update');
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->allowed($user, 'delete');
    }

    public function deleteAny(User $user): bool
    {
        return $this->allowed($user, 'delete');
    }

    private function allowed(User $user, string $action): bool
    {
        return in_array($action, AdminPermissions::actions($this->resource), true)
            && $user->hasAdminPermission($this->resource.'.'.$action);
    }
}

<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AccessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $user->isAdministrator() && ! ($record instanceof Role && $record->is_admin);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->update($user, $record) && ($record instanceof Role
            ? ! $record->users()->exists()
            : $record->getKey() !== $user->getKey());
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}

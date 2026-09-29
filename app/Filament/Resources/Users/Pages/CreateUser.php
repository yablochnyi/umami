<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        static::getResource()::authorizeCreate();
        $user = new User;
        $user->forceFill(Arr::only($data, ['name', 'email', 'password', 'role_id', 'admin_locale']));
        $user->save();

        return $user;
    }
}

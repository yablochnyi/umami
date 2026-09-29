<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        static::getResource()::authorizeEdit($record);
        // An administrator cannot remove their own access, including via a forged Livewire payload.
        if ($record->getKey() === auth()->id()) {
            unset($data['role_id']);
        }
        $record->forceFill(Arr::only($data, ['name', 'email', 'password', 'role_id', 'admin_locale']))->save();

        return $record;
    }
}

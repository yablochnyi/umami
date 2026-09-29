<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ['value' => $data['value']];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        static::getResource()::authorizeEdit($record);
        $record->update(['value' => $data['value'] ?? null]);

        return $record;
    }
}

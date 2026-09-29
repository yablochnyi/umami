<?php

namespace App\Filament\Resources\SiteTexts\Pages;

use App\Filament\Resources\SiteTexts\SiteTextResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSiteText extends EditRecord
{
    protected static string $resource = SiteTextResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ['value' => $this->record->getTranslations('value')];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        static::getResource()::authorizeEdit($record);
        foreach (['pl', 'uk', 'en'] as $locale) {
            $record->setTranslation('value', $locale, $data['value'][$locale]);
        }
        $record->save();

        return $record;
    }
}

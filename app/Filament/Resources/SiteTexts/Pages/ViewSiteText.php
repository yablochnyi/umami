<?php

namespace App\Filament\Resources\SiteTexts\Pages;

use App\Filament\Resources\SiteTexts\SiteTextResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSiteText extends ViewRecord
{
    protected static string $resource = SiteTextResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ['value' => $this->record->getTranslations('value')];
    }
}

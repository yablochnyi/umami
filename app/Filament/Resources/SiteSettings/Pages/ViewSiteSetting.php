<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSiteSetting extends ViewRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ['value' => $data['value']];
    }
}

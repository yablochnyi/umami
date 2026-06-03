<?php

namespace App\Filament\Resources\SiteTexts\Pages;

use App\Filament\Resources\SiteTexts\SiteTextResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSiteText extends EditRecord
{
    protected static string $resource = SiteTextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\SiteTexts\Schemas;

use App\Models\SiteText;
use App\Support\SiteTextCatalog;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteTextForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components(fn (?SiteText $record): array => $record ? [
            Section::make(SiteTextCatalog::label($record))
                ->description($record->key === 'hoursValue' ? __('texts.hours_note') : null)
                ->columns(['default' => 1, 'xl' => 3])
                ->schema(array_map(
                    fn (string $locale) => Textarea::make('value.'.$locale)
                        ->label(__('texts.languages.'.$locale))->rows(8)->required(),
                    ['pl', 'uk', 'en'],
                )),
        ] : []);
    }
}

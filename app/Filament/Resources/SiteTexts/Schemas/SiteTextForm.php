<?php

namespace App\Filament\Resources\SiteTexts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteTextForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Meta')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('group')->required()->default('general'),
                            TextInput::make('key')->required(),
                            TextInput::make('label')->required(),
                            TextInput::make('type')->required()->default('text'),
                            TextInput::make('sort_order')->label('Sort')->numeric()->default(0)->required(),
                        ]),
                    ]),
                Section::make('Value')
                    ->schema([
                        Grid::make(3)->schema([
                            Textarea::make('value.pl')->label('PL')->rows(5)->required(),
                            Textarea::make('value.uk')->label('UA')->rows(5)->required(),
                            Textarea::make('value.en')->label('EN')->rows(5)->required(),
                        ]),
                    ]),
            ]);
    }
}

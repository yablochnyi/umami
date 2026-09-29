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
                Section::make(__('Meta'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('group')->label(__('Group'))->required()->default('general'),
                            TextInput::make('key')->label(__('Key'))->required(),
                            TextInput::make('label')->label(__('Label'))->required(),
                            TextInput::make('type')->label(__('Type'))->required()->default('text'),
                            TextInput::make('sort_order')->label(__('Sort'))->numeric()->default(0)->required(),
                        ]),
                    ]),
                Section::make(__('Value'))
                    ->schema([
                        Grid::make(3)->schema([
                            Textarea::make('value.pl')->label(__('PL'))->rows(5)->required(),
                            Textarea::make('value.uk')->label(__('UA'))->rows(5)->required(),
                            Textarea::make('value.en')->label(__('EN'))->rows(5)->required(),
                        ]),
                    ]),
            ]);
    }
}

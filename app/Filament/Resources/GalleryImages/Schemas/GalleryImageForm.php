<?php

namespace App\Filament\Resources\GalleryImages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('labels.title'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('title.pl')->label(__('PL'))->required(),
                            TextInput::make('title.uk')->label(__('UA'))->required(),
                            TextInput::make('title.en')->label(__('EN'))->required(),
                        ]),
                    ]),
                Section::make(__('labels.alt_text'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('alt.pl')->label(__('PL')),
                            TextInput::make('alt.uk')->label(__('UA')),
                            TextInput::make('alt.en')->label(__('EN')),
                        ]),
                    ]),
                Section::make(__('labels.image'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('labels.image'))
                            ->disk('public')
                            ->directory('umami/gallery')
                            ->visibility('public')
                            ->image()
                            ->required()
                            ->maxSize(4096),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')->label(__('labels.sort'))->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label(__('labels.active'))->default(true),
                        ]),
                    ]),
            ]);
    }
}

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
                Section::make('Title')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('title.pl')->label('PL')->required(),
                            TextInput::make('title.uk')->label('UA')->required(),
                            TextInput::make('title.en')->label('EN')->required(),
                        ]),
                    ]),
                Section::make('Alt text')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('alt.pl')->label('PL'),
                            TextInput::make('alt.uk')->label('UA'),
                            TextInput::make('alt.en')->label('EN'),
                        ]),
                    ]),
                Section::make('Image')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Image')
                            ->disk('public')
                            ->directory('umami/gallery')
                            ->visibility('public')
                            ->image()
                            ->required()
                            ->maxSize(4096),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')->label('Sort')->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label('Active')->default(true),
                        ]),
                    ]),
            ]);
    }
}

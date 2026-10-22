<?php

namespace App\Filament\Resources\SocialLinks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SocialLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('labels.link'))
                    ->schema([
                        TextInput::make('label')->label(__('labels.label'))->required(),
                        TextInput::make('url')->label(__('labels.url'))->url()->required(),
                        FileUpload::make('icon')
                            ->label(__('labels.icon'))
                            ->disk('public')
                            ->directory('umami/icons')
                            ->visibility('public')
                            ->image()
                            ->maxSize(1024),
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')->label(__('labels.sort'))->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label(__('labels.active'))->default(true),
                        ]),
                    ]),
            ]);
    }
}

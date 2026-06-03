<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettingForm
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
                        Textarea::make('value')
                            ->rows(3)
                            ->hidden(fn ($get): bool => in_array($get('type'), ['image', 'video'], true))
                            ->columnSpanFull(),
                        FileUpload::make('value')
                            ->label(fn ($get): string => $get('type') === 'video' ? 'Video' : 'Image')
                            ->disk('public')
                            ->directory('umami/settings')
                            ->visibility('public')
                            ->acceptedFileTypes(fn ($get): array => $get('type') === 'video'
                                ? ['video/mp4', 'video/webm', 'video/quicktime']
                                : ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->visible(fn ($get): bool => in_array($get('type'), ['image', 'video'], true))
                            ->maxSize(51200)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

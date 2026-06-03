<?php

namespace App\Filament\Resources\MenuCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Name')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name.pl')->label('PL')->required(),
                            TextInput::make('name.uk')->label('UA')->required(),
                            TextInput::make('name.en')->label('EN')->required(),
                        ]),
                    ]),
                Section::make('Settings')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('slug')->required()->maxLength(255),
                            TextInput::make('sort_order')->label('Sort')->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label('Active')->default(true),
                        ]),
                    ]),
            ]);
    }
}

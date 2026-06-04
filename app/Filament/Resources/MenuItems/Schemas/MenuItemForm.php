<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Models\MenuCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->schema([
                        Select::make('menu_category_id')
                            ->label('Category')
                            ->options(fn () => MenuCategory::query()
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(fn (MenuCategory $category) => [$category->id => $category->getTranslation('name', 'pl')])
                                ->all())
                            ->searchable()
                            ->required(),
                        Grid::make(3)->schema([
                            TextInput::make('price')->label('Price')->maxLength(255),
                            TextInput::make('sort_order')->label('Sort')->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label('Active')->default(true),
                        ]),
                        Toggle::make('is_bestseller')->label('Show in bestsellers'),
                    ]),
                Section::make('Name')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name.pl')->label('PL')->required(),
                            TextInput::make('name.uk')->label('UA')->required(),
                            TextInput::make('name.en')->label('EN')->required(),
                        ]),
                        TextInput::make('slug')
                            ->label('SEO slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make('Description')
                    ->schema([
                        Grid::make(3)->schema([
                            Textarea::make('description.pl')->label('PL')->rows(5),
                            Textarea::make('description.uk')->label('UA')->rows(5),
                            Textarea::make('description.en')->label('EN')->rows(5),
                        ]),
                    ]),
                Section::make('Appetizing SEO description')
                    ->schema([
                        Grid::make(3)->schema([
                            Textarea::make('marketing_description.pl')->label('PL')->rows(6),
                            Textarea::make('marketing_description.uk')->label('UA')->rows(6),
                            Textarea::make('marketing_description.en')->label('EN')->rows(6),
                        ]),
                    ]),
                Section::make('Images')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Photo')
                            ->disk('public')
                            ->directory('umami/menu')
                            ->visibility('public')
                            ->image()
                            ->maxSize(4096),
                        TextInput::make('source_image')->label('Source image URL')->maxLength(255),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Filament\Schemas\MenuSchedule;
use App\Models\MenuCategory;
use App\Services\GoOrder\MenuAvailability;
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
                MenuSchedule::make(),
                Section::make(__('GoOrder'))->schema([
                    Toggle::make('goorder_published')->label(__('Present on storefront'))->disabled()->dehydrated(false),
                    TextInput::make('goorder_synced_at')->label(__('Last synchronization'))->disabled()->dehydrated(false),
                    Textarea::make('storefront_schedule')->label(__('Availability hours (GoOrder and local)'))->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record ? app(MenuAvailability::class)->label($record) : ''),
                ]),
                Section::make(__('Category'))
                    ->schema([
                        Select::make('menu_category_id')
                            ->label(__('Category'))
                            ->options(fn () => MenuCategory::query()
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(fn (MenuCategory $category) => [$category->id => $category->getTranslation('name', app()->getLocale())])
                                ->all())
                            ->searchable()
                            ->required(),
                        Grid::make(3)->schema([
                            TextInput::make('price')->label(__('Price'))->maxLength(255),
                            TextInput::make('sort_order')->label(__('Sort'))->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label(__('Active'))->default(true),
                        ]),
                        Toggle::make('is_bestseller')->label(__('Show in bestsellers')),
                    ]),
                Section::make(__('Name'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name.pl')->label(__('PL'))->required(),
                            TextInput::make('name.uk')->label(__('UA'))->required(),
                            TextInput::make('name.en')->label(__('EN'))->required(),
                        ]),
                        TextInput::make('slug')
                            ->label(__('SEO slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make(__('Description'))
                    ->schema([
                        Grid::make(3)->schema([
                            Textarea::make('description.pl')->label(__('PL'))->rows(5),
                            Textarea::make('description.uk')->label(__('UA'))->rows(5),
                            Textarea::make('description.en')->label(__('EN'))->rows(5),
                        ]),
                    ]),
                Section::make(__('Appetizing SEO description'))
                    ->schema([
                        Grid::make(3)->schema([
                            Textarea::make('marketing_description.pl')->label(__('PL'))->rows(6),
                            Textarea::make('marketing_description.uk')->label(__('UA'))->rows(6),
                            Textarea::make('marketing_description.en')->label(__('EN'))->rows(6),
                        ]),
                    ]),
                Section::make(__('SEO meta tags'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('seo_title.pl')->label(__('Meta title PL'))->maxLength(70),
                            TextInput::make('seo_title.uk')->label(__('Meta title UA'))->maxLength(70),
                            TextInput::make('seo_title.en')->label(__('Meta title EN'))->maxLength(70),
                        ]),
                        Grid::make(3)->schema([
                            Textarea::make('seo_description.pl')->label(__('Meta description PL'))->rows(3)->maxLength(170),
                            Textarea::make('seo_description.uk')->label(__('Meta description UA'))->rows(3)->maxLength(170),
                            Textarea::make('seo_description.en')->label(__('Meta description EN'))->rows(3)->maxLength(170),
                        ]),
                    ]),
                Section::make(__('Images'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('Photo'))
                            ->disk('public')
                            ->directory('umami/menu')
                            ->visibility('public')
                            ->image()
                            ->maxSize(4096),
                        TextInput::make('source_image')->label(__('Source image URL'))->maxLength(255),
                    ]),
            ]);
    }
}

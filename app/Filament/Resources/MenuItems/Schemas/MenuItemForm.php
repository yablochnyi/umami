<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Filament\Schemas\MenuSchedule;
use App\Filament\Schemas\TranslationTabs;
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
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('dish-description', fn (string $locale) => [
                            Textarea::make('description.'.$locale)->label(__('Description'))->rows(8)->autosize(),
                        ]),
                    ]),
                Section::make(__('Appetizing SEO description'))
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('dish-marketing', fn (string $locale) => [
                            Textarea::make('marketing_description.'.$locale)->label(__('Appetizing SEO description'))->rows(8)->autosize(),
                        ]),
                    ]),
                Section::make(__('SEO meta tags'))
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('dish-seo', fn (string $locale) => [
                            TextInput::make('seo_title.'.$locale)->label(__('Meta title '.($locale === 'uk' ? 'UA' : strtoupper($locale))))->maxLength(70),
                            Textarea::make('seo_description.'.$locale)->label(__('Meta description '.($locale === 'uk' ? 'UA' : strtoupper($locale))))->rows(4)->autosize()->maxLength(170),
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

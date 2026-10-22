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
                    Toggle::make('goorder_published')->label(__('labels.present_on_storefront'))->disabled()->dehydrated(false),
                    TextInput::make('goorder_synced_at')->label(__('labels.last_synchronization'))->disabled()->dehydrated(false),
                    Textarea::make('storefront_schedule')->label(__('labels.availability_hours_goorder_and_local'))->disabled()->dehydrated(false)
                        ->formatStateUsing(fn ($record) => $record ? app(MenuAvailability::class)->label($record) : ''),
                ]),
                Section::make(__('labels.category'))
                    ->schema([
                        Select::make('menu_category_id')
                            ->label(__('labels.category'))
                            ->options(fn () => MenuCategory::query()
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(fn (MenuCategory $category) => [$category->id => $category->getTranslation('name', app()->getLocale())])
                                ->all())
                            ->searchable()
                            ->required(),
                        Grid::make(3)->schema([
                            TextInput::make('price')->label(__('labels.price'))->maxLength(255),
                            TextInput::make('sort_order')->label(__('labels.sort'))->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label(__('labels.active'))->default(true),
                        ]),
                        Toggle::make('is_bestseller')->label(__('labels.show_in_bestsellers')),
                    ]),
                Section::make(__('labels.name'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name.pl')->label(__('PL'))->required(),
                            TextInput::make('name.uk')->label(__('UA'))->required(),
                            TextInput::make('name.en')->label(__('EN'))->required(),
                        ]),
                        TextInput::make('slug')
                            ->label(__('labels.seo_slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make(__('labels.description'))
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('dish-description', fn (string $locale) => [
                            Textarea::make('description.'.$locale)->label(__('labels.description'))->rows(8)->autosize(),
                        ]),
                    ]),
                Section::make(__('labels.appetizing_seo_description'))
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('dish-marketing', fn (string $locale) => [
                            Textarea::make('marketing_description.'.$locale)->label(__('labels.appetizing_seo_description'))->rows(8)->autosize(),
                        ]),
                    ]),
                Section::make(__('labels.seo_meta_tags'))
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('dish-seo', fn (string $locale) => [
                            TextInput::make('seo_title.'.$locale)->label(__('labels.meta_title_'.($locale === 'uk' ? 'ua' : $locale)))->maxLength(70),
                            Textarea::make('seo_description.'.$locale)->label(__('labels.meta_description_'.($locale === 'uk' ? 'ua' : $locale)))->rows(4)->autosize()->maxLength(170),
                        ]),
                    ]),
                Section::make(__('labels.images'))
                    ->schema([
                        FileUpload::make('image')
                            ->label(__('labels.photo'))
                            ->disk('public')
                            ->directory('umami/menu')
                            ->visibility('public')
                            ->image()
                            ->maxSize(4096),
                        TextInput::make('source_image')->label(__('labels.source_image_url'))->maxLength(255),
                    ]),
            ]);
    }
}

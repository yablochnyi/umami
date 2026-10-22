<?php

namespace App\Filament\Resources\MenuCategories\Schemas;

use App\Filament\Schemas\MenuSchedule;
use App\Filament\Schemas\TranslationTabs;
use Filament\Forms\Components\Textarea;
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
                MenuSchedule::make(),
                Section::make(__('Name'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name.pl')->label(__('PL'))->required(),
                            TextInput::make('name.uk')->label(__('UA'))->required(),
                            TextInput::make('name.en')->label(__('EN'))->required(),
                        ]),
                    ]),
                Section::make(__('Settings'))
                    ->schema([
                        Toggle::make('goorder_import_enabled')->label(__('Include this GoOrder category'))->default(true),
                        Grid::make(3)->schema([
                            TextInput::make('slug')->label(__('Slug'))->required()->maxLength(255),
                            TextInput::make('sort_order')->label(__('Sort'))->numeric()->default(0)->required(),
                            Toggle::make('is_active')->label(__('Active'))->default(true),
                        ]),
                    ]),
                Section::make(__('SEO intro before dishes'))
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('category-intro', fn (string $locale) => [
                            Textarea::make('intro_text.'.$locale)->label(__('SEO intro before dishes'))->rows(6)->autosize(),
                        ]),
                    ]),
                Section::make(__('SEO text after dishes'))
                    ->columnSpanFull()
                    ->schema([
                        TranslationTabs::make('category-seo', fn (string $locale) => [
                            Textarea::make('seo_text.'.$locale)->label(__('SEO text after dishes'))->rows(10)->autosize(),
                        ]),
                    ]),
            ]);
    }
}

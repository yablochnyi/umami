<?php

namespace App\Filament\Resources\MenuItems\Tables;

use App\Models\MenuCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label(__('labels.photo'))->disk('public')->square(),
                TextColumn::make('name')
                    ->label(__('labels.name'))
                    ->getStateUsing(fn ($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('labels.slug'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->label(__('labels.category'))
                    ->getStateUsing(fn ($record) => $record->category?->getTranslation('name', app()->getLocale())),
                TextColumn::make('price')->label(__('labels.price'))->sortable(),
                TextColumn::make('sort_order')->label(__('labels.sort'))->sortable(),
                IconColumn::make('is_bestseller')->label(__('labels.best'))->boolean(),
                IconColumn::make('is_active')->label(__('labels.active'))->boolean(),
                IconColumn::make('goorder_published')->label(__('GoOrder'))->boolean(),
            ])
            ->filters([
                SelectFilter::make('menu_category_id')
                    ->label(__('labels.category'))
                    ->options(fn () => MenuCategory::query()
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (MenuCategory $category) => [$category->id => $category->getTranslation('name', app()->getLocale())])
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

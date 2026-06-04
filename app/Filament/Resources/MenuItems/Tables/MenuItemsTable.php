<?php

namespace App\Filament\Resources\MenuItems\Tables;

use App\Models\MenuCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
                ImageColumn::make('image')->label('Photo')->disk('public')->square(),
                TextColumn::make('name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => $record->getTranslation('name', 'pl'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->label('Category')
                    ->getStateUsing(fn ($record) => $record->category?->getTranslation('name', 'pl')),
                TextColumn::make('price')->label('Price')->sortable(),
                TextColumn::make('sort_order')->label('Sort')->sortable(),
                IconColumn::make('is_bestseller')->label('Best')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('menu_category_id')
                    ->label('Category')
                    ->options(fn () => MenuCategory::query()
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (MenuCategory $category) => [$category->id => $category->getTranslation('name', 'pl')])
                        ->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\SiteTexts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteTextsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')->label(__('Group'))
                    ->searchable(),
                TextColumn::make('key')->label(__('Key'))
                    ->searchable(),
                TextColumn::make('label')->label(__('Label'))
                    ->searchable(),
                TextColumn::make('value')
                    ->label(__('PL value'))
                    ->getStateUsing(fn ($record) => str($record->getTranslation('value', app()->getLocale()))->limit(70)),
                TextColumn::make('type')->label(__('Type'))
                    ->searchable(),
                TextColumn::make('sort_order')->label(__('Sort'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

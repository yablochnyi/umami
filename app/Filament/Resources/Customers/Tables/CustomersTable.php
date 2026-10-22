<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(__('labels.customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('labels.phone'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('labels.email'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->label(__('labels.city'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('orders_count')
                    ->visible(fn () => auth()->user()?->hasAdminPermission('orders.view'))
                    ->label(__('labels.orders'))
                    ->counts('orders')
                    ->sortable(),
                TextColumn::make('gopos_id')
                    ->label(__('GoPOS ID'))
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('labels.added_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}

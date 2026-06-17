<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('gopos_number')
                    ->label('Nr GoPOS')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('number')
                    ->label('Nr wewn.')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer.name')
                    ->label('Klient')
                    ->searchable(),
                TextColumn::make('customer.phone')
                    ->label('Telefon')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Razem')
                    ->money('PLN')
                    ->sortable(),
                TextColumn::make('delivery_type')
                    ->label('Typ')
                    ->formatStateUsing(fn (?string $state): string => $state === 'delivery' ? 'Dostawa' : 'Na wynos'),
                TextColumn::make('payment_type')
                    ->label('Płatność')
                    ->formatStateUsing(fn (?string $state): string => $state === 'cash' ? 'Gotówka' : 'Karta'),
                IconColumn::make('wants_invoice')
                    ->label('Faktura')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent_to_gopos' => 'success',
                        'gopos_error' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sent_to_gopos' => 'Wysłane',
                        'gopos_error' => 'Błąd',
                        default => 'Nowe',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'new' => 'Nowe lokalnie',
                        'sent_to_gopos' => 'Wysłane do GoPOS',
                        'gopos_error' => 'Błąd GoPOS',
                    ]),
                SelectFilter::make('delivery_type')
                    ->label('Typ')
                    ->options([
                        'pickup' => 'Na wynos',
                        'delivery' => 'Dostawa',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Podgląd'),
            ]);
    }
}

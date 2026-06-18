<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Zamówienia klienta';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('gopos_number')
                    ->label('Nr GoPOS')
                    ->placeholder('-'),
                TextColumn::make('total')
                    ->label('Razem')
                    ->money('PLN'),
                TextColumn::make('delivery_type')
                    ->label('Typ')
                    ->formatStateUsing(fn (?string $state): string => $state === 'delivery' ? 'Dostawa' : 'Na wynos'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => 'info',
                        'sent_to_gopos' => 'success',
                        'gopos_error' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => 'Czeka w GoPOS',
                        'sent_to_gopos' => 'Wysłane',
                        'gopos_error' => 'Błąd',
                        default => 'Nowe',
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}

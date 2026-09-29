<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->label(__('Data'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('gopos_number')
                    ->label(__('Nr GoPOS'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('number')
                    ->label(__('Nr wewn.'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer.name')
                    ->label(__('Klient'))
                    ->searchable(),
                TextColumn::make('customer.phone')
                    ->label(__('Telefon'))
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('Razem'))
                    ->money('PLN')
                    ->sortable(),
                TextColumn::make('delivery_type')
                    ->label(__('Typ'))
                    ->formatStateUsing(fn (?string $state): string => $state === 'delivery' ? __('Dostawa') : __('Na wynos')),
                TextColumn::make('payment_type')
                    ->label(__('Płatność'))
                    ->formatStateUsing(fn (?string $state): string => $state === 'cash' ? __('Gotówka') : __('Karta')),
                IconColumn::make('wants_invoice')
                    ->label(__('Faktura'))
                    ->boolean(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => 'info',
                        'sent_to_gopos' => 'success',
                        'gopos_error' => 'danger',
                        'accepted', 'ready', 'delivering', 'completed' => 'success',
                        'goorder_failed', 'rejected', 'canceled', 'submission_uncertain' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => __('Czeka w GoPOS'),
                        'sent_to_gopos' => __('Wysłane'),
                        'gopos_error' => __('Błąd'),
                        default => trans('tracking.states.'.$state),
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'new' => __('Nowe lokalnie'),
                        'waiting_gopos_acceptance' => __('Czeka na potwierdzenie w GoPOS'),
                        'sent_to_gopos' => __('Wysłane do GoPOS'),
                        'gopos_error' => __('Błąd GoPOS'),
                        ...trans('tracking.states'),
                    ]),
                SelectFilter::make('delivery_type')
                    ->label(__('Typ'))
                    ->options([
                        'pickup' => __('Na wynos'),
                        'delivery' => __('Dostawa'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}

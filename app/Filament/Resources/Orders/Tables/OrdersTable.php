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
                    ->label(__('labels.date'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('gopos_number')
                    ->label(__('labels.gopos_number'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('number')
                    ->label(__('labels.internal_number_short'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer.name')
                    ->label(__('labels.customer'))
                    ->searchable(),
                TextColumn::make('customer.phone')
                    ->label(__('labels.phone'))
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('labels.total'))
                    ->money('PLN')
                    ->sortable(),
                TextColumn::make('delivery_type')
                    ->label(__('labels.type'))
                    ->formatStateUsing(fn (?string $state): string => $state === 'delivery' ? __('labels.delivery') : __('labels.takeaway')),
                TextColumn::make('payment_type')
                    ->label(__('labels.payment'))
                    ->formatStateUsing(fn (?string $state): string => $state === 'cash' ? __('labels.cash') : __('labels.card')),
                IconColumn::make('wants_invoice')
                    ->label(__('labels.invoice'))
                    ->boolean(),
                TextColumn::make('status')
                    ->label(__('labels.status'))
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
                        'waiting_gopos_acceptance' => __('labels.waiting_in_gopos'),
                        'sent_to_gopos' => __('labels.sent'),
                        'gopos_error' => __('labels.error'),
                        default => trans('tracking.states.'.$state),
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('labels.status'))
                    ->options([
                        'new' => __('labels.new_locally'),
                        'waiting_gopos_acceptance' => __('labels.awaiting_gopos_confirmation'),
                        'sent_to_gopos' => __('labels.sent_to_gopos'),
                        'gopos_error' => __('labels.gopos_error'),
                        ...trans('tracking.states'),
                    ]),
                SelectFilter::make('delivery_type')
                    ->label(__('labels.type'))
                    ->options([
                        'pickup' => __('labels.takeaway'),
                        'delivery' => __('labels.delivery'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}

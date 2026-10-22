<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Zamówienia klienta';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('labels.customer_orders');
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return Gate::allows('viewAny', Order::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('labels.date'))
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('gopos_number')
                    ->label(__('labels.gopos_number'))
                    ->placeholder('-'),
                TextColumn::make('total')
                    ->label(__('labels.total'))
                    ->money('PLN'),
                TextColumn::make('delivery_type')
                    ->label(__('labels.type'))
                    ->formatStateUsing(fn (?string $state): string => $state === 'delivery' ? __('labels.delivery') : __('labels.takeaway')),
                TextColumn::make('status')
                    ->label(__('labels.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => 'info',
                        'sent_to_gopos' => 'success',
                        'gopos_error' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => __('labels.waiting_in_gopos'),
                        'sent_to_gopos' => __('labels.sent'),
                        'gopos_error' => __('labels.error'),
                        default => trans('tracking.states.'.$state),
                    }),
            ])
            ->recordActions([
                Action::make('view')->label(__('labels.preview'))->icon('heroicon-o-eye')
                    ->url(fn ($record) => OrderResource::getUrl('view', ['record' => $record]))
                    ->authorize('view'),
            ]);
    }
}

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
        return __('Zamówienia klienta');
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
                    ->label(__('Data'))
                    ->dateTime('d.m.Y H:i'),
                TextColumn::make('gopos_number')
                    ->label(__('Nr GoPOS'))
                    ->placeholder('-'),
                TextColumn::make('total')
                    ->label(__('Razem'))
                    ->money('PLN'),
                TextColumn::make('delivery_type')
                    ->label(__('Typ'))
                    ->formatStateUsing(fn (?string $state): string => $state === 'delivery' ? __('Dostawa') : __('Na wynos')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => 'info',
                        'sent_to_gopos' => 'success',
                        'gopos_error' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_gopos_acceptance' => __('Czeka w GoPOS'),
                        'sent_to_gopos' => __('Wysłane'),
                        'gopos_error' => __('Błąd'),
                        default => trans('tracking.states.'.$state),
                    }),
            ])
            ->recordActions([
                Action::make('view')->label(__('Podgląd'))->icon('heroicon-o-eye')
                    ->url(fn ($record) => OrderResource::getUrl('view', ['record' => $record]))
                    ->authorize('view'),
            ]);
    }
}

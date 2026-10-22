<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('labels.status'))
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('number')->label(__('labels.internal_number'))->disabled(),
                            TextInput::make('gopos_number')->label(__('labels.gopos_order_number'))->disabled(),
                            TextInput::make('gopos_id')->label(__('GoPOS ID'))->disabled(),
                            Select::make('status')
                                ->label(__('labels.status'))
                                ->options([
                                    'new' => __('labels.new_locally'),
                                    'waiting_gopos_acceptance' => __('labels.awaiting_gopos_confirmation'),
                                    'sent_to_gopos' => __('labels.sent_to_gopos'),
                                    'gopos_error' => __('labels.gopos_error'),
                                    ...trans('tracking.states'),
                                ])
                                ->disabled(fn ($record): bool => filled($record?->tracking_token))
                                ->required(),
                        ]),
                        Textarea::make('gopos_error')
                            ->label(__('labels.gopos_error'))
                            ->rows(3)
                            ->disabled()
                            ->visible(fn ($record): bool => filled($record?->gopos_error)),
                        TextInput::make('goorder_id')->label(__('GoOrder ID'))->disabled()->visible(fn ($record): bool => filled($record?->tracking_token)),
                        TextInput::make('goorder_error')->label(__('labels.goorder_diagnostics'))->disabled()->visible(fn ($record): bool => filled($record?->goorder_error)),
                        TextInput::make('expected_ready_at')->label(__('labels.expected_time_utc'))->disabled()->visible(fn ($record): bool => filled($record?->tracking_token)),
                    ]),
                Section::make(__('labels.customer'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('customer.name')->label(__('labels.full_name'))->disabled(),
                            TextInput::make('customer.phone')->label(__('labels.phone'))->disabled(),
                            TextInput::make('customer.email')->label(__('labels.email'))->disabled(),
                        ]),
                        Grid::make(3)->schema([
                            Toggle::make('wants_invoice')->label(__('labels.invoice'))->disabled(),
                            TextInput::make('nip')->label(__('labels.tax_id'))->disabled(),
                            TextInput::make('payment_type')->label(__('labels.payment'))->formatStateUsing(fn ($state) => __('labels')[$state] ?? $state)->disabled(),
                        ]),
                    ]),
                Section::make(__('labels.pickup_and_delivery'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('delivery_type')->label(__('labels.type'))->formatStateUsing(fn ($state) => __('labels')[$state] ?? $state)->disabled(),
                            TextInput::make('fulfillment_type')->label(__('labels.fulfillment_time'))->formatStateUsing(fn ($state) => __('labels')[$state] ?? $state)->disabled(),
                            TextInput::make('scheduled_at')->label(__('labels.scheduled_at'))->disabled(),
                        ]),
                        Grid::make(4)->schema([
                            TextInput::make('city')->label(__('labels.city'))->disabled(),
                            TextInput::make('street')->label(__('labels.street'))->disabled(),
                            TextInput::make('building_number')->label(__('labels.building_number'))->disabled(),
                            TextInput::make('apartment_number')->label(__('labels.apartment_number'))->disabled(),
                        ]),
                        Textarea::make('comment')->label(__('labels.customer_comment'))->rows(3)->disabled(),
                    ]),
                Section::make(__('labels.products'))
                    ->schema([
                        Repeater::make('items')
                            ->label(__('labels.order_items'))
                            ->relationship()
                            ->disabled()
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('name')->label(__('labels.product'))->disabled(),
                                    TextInput::make('quantity')->label(__('labels.quantity'))->disabled(),
                                    TextInput::make('unit_price')->label(__('labels.price'))->suffix('zł')->disabled(),
                                    TextInput::make('total')->label(__('labels.amount'))->suffix('zł')->disabled(),
                                ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(1),
                    ]),
                Section::make(__('labels.amounts'))
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('subtotal')->label(__('labels.products'))->suffix('zł')->disabled(),
                            TextInput::make('delivery_cost')->label(__('labels.delivery'))->suffix('zł')->disabled(),
                            TextInput::make('total')->label(__('labels.total'))->suffix('zł')->disabled(),
                            TextInput::make('created_at')->label(__('labels.created_at'))->disabled(),
                        ]),
                    ]),
            ]);
    }
}

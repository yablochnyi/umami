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
                Section::make(__('Status'))
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('number')->label(__('Numer wewnętrzny'))->disabled(),
                            TextInput::make('gopos_number')->label(__('Numer GoPOS'))->disabled(),
                            TextInput::make('gopos_id')->label(__('GoPOS ID'))->disabled(),
                            Select::make('status')
                                ->label(__('Status'))
                                ->options([
                                    'new' => __('Nowe lokalnie'),
                                    'waiting_gopos_acceptance' => __('Czeka na potwierdzenie w GoPOS'),
                                    'sent_to_gopos' => __('Wysłane do GoPOS'),
                                    'gopos_error' => __('Błąd GoPOS'),
                                    ...trans('tracking.states'),
                                ])
                                ->disabled(fn ($record): bool => filled($record?->tracking_token))
                                ->required(),
                        ]),
                        Textarea::make('gopos_error')
                            ->label(__('Błąd GoPOS'))
                            ->rows(3)
                            ->disabled()
                            ->visible(fn ($record): bool => filled($record?->gopos_error)),
                        TextInput::make('goorder_id')->label(__('GoOrder ID'))->disabled()->visible(fn ($record): bool => filled($record?->tracking_token)),
                        TextInput::make('goorder_error')->label(__('GoOrder: diagnostyka'))->disabled()->visible(fn ($record): bool => filled($record?->goorder_error)),
                        TextInput::make('expected_ready_at')->label(__('Przewidywany czas (UTC)'))->disabled()->visible(fn ($record): bool => filled($record?->tracking_token)),
                    ]),
                Section::make(__('Klient'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('customer.name')->label(__('Imię i nazwisko'))->disabled(),
                            TextInput::make('customer.phone')->label(__('Telefon'))->disabled(),
                            TextInput::make('customer.email')->label(__('E-mail'))->disabled(),
                        ]),
                        Grid::make(3)->schema([
                            Toggle::make('wants_invoice')->label(__('Faktura'))->disabled(),
                            TextInput::make('nip')->label(__('NIP'))->disabled(),
                            TextInput::make('payment_type')->label(__('Płatność'))->formatStateUsing(fn ($state) => __($state ?? ''))->disabled(),
                        ]),
                    ]),
                Section::make(__('Odbiór i dostawa'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('delivery_type')->label(__('Typ'))->formatStateUsing(fn ($state) => __($state ?? ''))->disabled(),
                            TextInput::make('fulfillment_type')->label(__('Termin'))->formatStateUsing(fn ($state) => __($state ?? ''))->disabled(),
                            TextInput::make('scheduled_at')->label(__('Zaplanowano'))->disabled(),
                        ]),
                        Grid::make(4)->schema([
                            TextInput::make('city')->label(__('Miasto'))->disabled(),
                            TextInput::make('street')->label(__('Ulica'))->disabled(),
                            TextInput::make('building_number')->label(__('Numer domu'))->disabled(),
                            TextInput::make('apartment_number')->label(__('Numer mieszkania'))->disabled(),
                        ]),
                        Textarea::make('comment')->label(__('Komentarz klienta'))->rows(3)->disabled(),
                    ]),
                Section::make(__('Produkty'))
                    ->schema([
                        Repeater::make('items')
                            ->label(__('Pozycje zamówienia'))
                            ->relationship()
                            ->disabled()
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('name')->label(__('Produkt'))->disabled(),
                                    TextInput::make('quantity')->label(__('Ilość'))->disabled(),
                                    TextInput::make('unit_price')->label(__('Cena'))->suffix('zł')->disabled(),
                                    TextInput::make('total')->label(__('Suma'))->suffix('zł')->disabled(),
                                ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(1),
                    ]),
                Section::make(__('Kwoty'))
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('subtotal')->label(__('Produkty'))->suffix('zł')->disabled(),
                            TextInput::make('delivery_cost')->label(__('Dostawa'))->suffix('zł')->disabled(),
                            TextInput::make('total')->label(__('Razem'))->suffix('zł')->disabled(),
                            TextInput::make('created_at')->label(__('Utworzono'))->disabled(),
                        ]),
                    ]),
            ]);
    }
}

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
                Section::make('Status')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('number')->label('Numer wewnętrzny')->disabled(),
                            TextInput::make('gopos_number')->label('Numer GoPOS')->disabled(),
                            TextInput::make('gopos_id')->label('GoPOS ID')->disabled(),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'new' => 'Nowe lokalnie',
                                    'sent_to_gopos' => 'Wysłane do GoPOS',
                                    'gopos_error' => 'Błąd GoPOS',
                                ])
                                ->required(),
                        ]),
                        Textarea::make('gopos_error')
                            ->label('Błąd GoPOS')
                            ->rows(3)
                            ->disabled()
                            ->visible(fn ($record): bool => filled($record?->gopos_error)),
                    ]),
                Section::make('Klient')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('customer.name')->label('Imię i nazwisko')->disabled(),
                            TextInput::make('customer.phone')->label('Telefon')->disabled(),
                            TextInput::make('customer.email')->label('E-mail')->disabled(),
                        ]),
                        Grid::make(3)->schema([
                            Toggle::make('wants_invoice')->label('Faktura')->disabled(),
                            TextInput::make('nip')->label('NIP')->disabled(),
                            TextInput::make('payment_type')->label('Płatność')->disabled(),
                        ]),
                    ]),
                Section::make('Odbiór i dostawa')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('delivery_type')->label('Typ')->disabled(),
                            TextInput::make('fulfillment_type')->label('Termin')->disabled(),
                            TextInput::make('scheduled_at')->label('Zaplanowano')->disabled(),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('street')->label('Ulica')->disabled(),
                            TextInput::make('building_number')->label('Numer domu')->disabled(),
                            TextInput::make('apartment_number')->label('Numer mieszkania')->disabled(),
                        ]),
                        Textarea::make('comment')->label('Komentarz klienta')->rows(3)->disabled(),
                    ]),
                Section::make('Produkty')
                    ->schema([
                        Repeater::make('items')
                            ->label('Pozycje zamówienia')
                            ->relationship()
                            ->disabled()
                            ->schema([
                                Grid::make(4)->schema([
                                    TextInput::make('name')->label('Produkt')->disabled(),
                                    TextInput::make('quantity')->label('Ilość')->disabled(),
                                    TextInput::make('unit_price')->label('Cena')->suffix('zł')->disabled(),
                                    TextInput::make('total')->label('Suma')->suffix('zł')->disabled(),
                                ]),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(1),
                    ]),
                Section::make('Kwoty')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('subtotal')->label('Produkty')->suffix('zł')->disabled(),
                            TextInput::make('delivery_cost')->label('Dostawa')->suffix('zł')->disabled(),
                            TextInput::make('total')->label('Razem')->suffix('zł')->disabled(),
                            TextInput::make('created_at')->label('Utworzono')->disabled(),
                        ]),
                    ]),
            ]);
    }
}

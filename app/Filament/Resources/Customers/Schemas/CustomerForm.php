<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Dane klienta'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')->label(__('Imię i nazwisko'))->required()->maxLength(255),
                            TextInput::make('phone')->label(__('Telefon'))->required()->maxLength(255),
                            TextInput::make('email')->label(__('E-mail'))->email()->required()->maxLength(255),
                        ]),
                        TextInput::make('nip')->label(__('NIP'))->maxLength(255),
                    ]),
                Section::make(__('Adres'))
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('city')->label(__('Miasto'))->maxLength(255),
                            TextInput::make('street')->label(__('Ulica'))->maxLength(255),
                            TextInput::make('building_number')->label(__('Numer domu'))->maxLength(255),
                            TextInput::make('apartment_number')->label(__('Numer mieszkania'))->maxLength(255),
                        ]),
                    ]),
                Section::make(__('GoPOS'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('gopos_id')->label(__('GoPOS ID'))->disabled(),
                            TextInput::make('gopos_synced_at')->label(__('Ostatnia synchronizacja'))->disabled(),
                            TextInput::make('created_at')->label(__('Utworzono'))->disabled(),
                        ]),
                    ])
                    ->collapsed(),
            ]);
    }
}

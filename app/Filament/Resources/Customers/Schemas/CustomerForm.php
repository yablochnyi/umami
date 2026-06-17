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
                Section::make('Dane klienta')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')->label('Imię i nazwisko')->required()->maxLength(255),
                            TextInput::make('phone')->label('Telefon')->required()->maxLength(255),
                            TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255),
                        ]),
                        TextInput::make('nip')->label('NIP')->maxLength(255),
                    ]),
                Section::make('Adres')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('street')->label('Ulica')->maxLength(255),
                            TextInput::make('building_number')->label('Numer domu')->maxLength(255),
                            TextInput::make('apartment_number')->label('Numer mieszkania')->maxLength(255),
                        ]),
                    ]),
                Section::make('GoPOS')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('gopos_id')->label('GoPOS ID')->disabled(),
                            TextInput::make('gopos_synced_at')->label('Ostatnia synchronizacja')->disabled(),
                            TextInput::make('created_at')->label('Utworzono')->disabled(),
                        ]),
                    ])
                    ->collapsed(),
            ]);
    }
}

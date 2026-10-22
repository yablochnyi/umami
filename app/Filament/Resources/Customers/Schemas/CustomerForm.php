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
                Section::make(__('labels.customer_details'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')->label(__('labels.full_name'))->required()->maxLength(255),
                            TextInput::make('phone')->label(__('labels.phone'))->required()->maxLength(255),
                            TextInput::make('email')->label(__('labels.email'))->email()->required()->maxLength(255),
                        ]),
                        TextInput::make('nip')->label(__('labels.tax_id'))->maxLength(255),
                    ]),
                Section::make(__('labels.address'))
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('city')->label(__('labels.city'))->maxLength(255),
                            TextInput::make('street')->label(__('labels.street'))->maxLength(255),
                            TextInput::make('building_number')->label(__('labels.building_number'))->maxLength(255),
                            TextInput::make('apartment_number')->label(__('labels.apartment_number'))->maxLength(255),
                        ]),
                    ]),
                Section::make(__('GoPOS'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('gopos_id')->label(__('GoPOS ID'))->disabled(),
                            TextInput::make('gopos_synced_at')->label(__('labels.last_synchronization'))->disabled(),
                            TextInput::make('created_at')->label(__('labels.created_at'))->disabled(),
                        ]),
                    ])
                    ->collapsed(),
            ]);
    }
}

<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class MenuSchedule
{
    public static function make(): Section
    {
        return Section::make('Local availability (Europe/Warsaw)')->schema([
            Toggle::make('schedule_enabled')->label('Restrict ordering to these hours')->live()->default(false),
            Repeater::make('schedule_hours')->label('Additional local hours; GoOrder limits still apply')
                ->visible(fn ($get) => $get('schedule_enabled'))->defaultItems(0)->minItems(1)
                ->required(fn ($get) => $get('schedule_enabled'))
                ->schema([
                    Select::make('days')->label('Days')->multiple()->required()->options([
                        1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
                    ]),
                    TimePicker::make('from')->label('From')->seconds(false)->format('H:i')->required(),
                    TimePicker::make('to')->label('Until')->seconds(false)->format('H:i')->different('from')->required(),
                ])->columns(3),
        ]);
    }
}

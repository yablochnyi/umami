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
        return Section::make(__('Local availability (Europe/Warsaw)'))->schema([
            Toggle::make('schedule_enabled')->label(__('Restrict ordering to these hours'))->live()->default(false),
            Repeater::make('schedule_hours')->label(__('Additional local hours; GoOrder limits still apply'))
                ->visible(fn ($get) => $get('schedule_enabled'))->defaultItems(0)->minItems(1)
                ->required(fn ($get) => $get('schedule_enabled'))
                ->schema([
                    Select::make('days')->label(__('Days'))->multiple()->required()->options([
                        1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'), 4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday'), 7 => __('Sunday'),
                    ]),
                    TimePicker::make('from')->label(__('From'))->seconds(false)->format('H:i')->required(),
                    TimePicker::make('to')->label(__('Until'))->seconds(false)->format('H:i')->different('from')->required(),
                ])->columns(3),
        ]);
    }
}

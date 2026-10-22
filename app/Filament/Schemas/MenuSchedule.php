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
        return Section::make(__('labels.local_availability_europewarsaw'))->schema([
            Toggle::make('schedule_enabled')->label(__('labels.restrict_ordering_to_these_hours'))->live()->default(false),
            Repeater::make('schedule_hours')->label(__('labels.additional_local_hours_goorder_limits_still_apply'))
                ->visible(fn ($get) => $get('schedule_enabled'))->defaultItems(0)->minItems(1)
                ->required(fn ($get) => $get('schedule_enabled'))
                ->schema([
                    Select::make('days')->label(__('labels.days'))->multiple()->required()->options([
                        1 => __('labels.monday'), 2 => __('labels.tuesday'), 3 => __('labels.wednesday'), 4 => __('labels.thursday'), 5 => __('labels.friday'), 6 => __('labels.saturday'), 7 => __('labels.sunday'),
                    ]),
                    TimePicker::make('from')->label(__('labels.from'))->seconds(false)->format('H:i')->required(),
                    TimePicker::make('to')->label(__('labels.until'))->seconds(false)->format('H:i')->different('from')->required(),
                ])->columns(3),
        ]);
    }
}

<?php

namespace App\Filament\Resources\SiteTexts\Tables;

use App\Models\SiteText;
use App\Support\SiteTextCatalog;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SiteTextsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('sort_order')
            ->columns([
                TextColumn::make('block')->label(__('texts.block'))
                    ->getStateUsing(fn (SiteText $record) => SiteTextCatalog::label($record))->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $labels = __('texts.blocks');
                        $keys = array_keys(array_filter($labels, fn ($label) => Str::contains(Str::lower($label), Str::lower($search))));

                        return $query->whereIn('key', $keys);
                    }),
                TextColumn::make('content')->label(__('texts.content'))
                    ->getStateUsing(fn (SiteText $record) => $record->getTranslation('value', app()->getLocale()))
                    ->limit(140)->wrap()->placeholder(__('texts.empty')),
            ])
            ->recordActions([ViewAction::make()->iconButton(), EditAction::make()->iconButton()]);
    }
}

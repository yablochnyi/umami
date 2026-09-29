<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use App\Models\SiteSetting;
use App\Support\SiteSettingCatalog as Catalog;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('sort_order')->paginated(false)
            ->columns([
                ImageColumn::make('preview')->label(__('settings.preview'))->disk('public')->imageSize(56)
                    ->getStateUsing(fn (SiteSetting $record) => Catalog::type($record) === 'image' ? $record->value : null),
                TextColumn::make('setting_name')->label(__('settings.setting'))
                    ->getStateUsing(fn (SiteSetting $record) => Catalog::label($record))
                    ->description(fn (SiteSetting $record) => __('settings.hints.'.$record->key))->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $keys = array_filter(array_keys(Catalog::FIELDS), fn ($key) => Str::contains(Str::lower(__('settings.labels.'.$key)), Str::lower($search)));

                        return $query->whereIn('key', $keys);
                    }),
                TextColumn::make('section')->label(__('settings.section'))->badge()->color('gray')
                    ->getStateUsing(fn (SiteSetting $record) => Catalog::group($record)),
                TextColumn::make('value')->label(__('settings.current_value'))->limit(40)->wrap()
                    ->formatStateUsing(fn (?string $state, SiteSetting $record) => in_array(Catalog::type($record), ['image', 'video'], true)
                        ? (filled($state) ? __('settings.file_uploaded') : __('settings.not_set')) : $state)
                    ->placeholder(__('settings.not_set')),
            ])->filters([
                SelectFilter::make('section')->label(__('settings.section'))
                    ->options(__('settings.groups'))
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'] ?? null, fn ($query, $group) => $query->whereIn('key', array_keys(array_filter(Catalog::FIELDS, fn ($field) => $field[1] === $group))))),
            ])->recordActions([ViewAction::make(), EditAction::make()]);
    }
}

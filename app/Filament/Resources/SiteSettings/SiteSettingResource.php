<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Filament\Resources\SiteSettings\Pages\ViewSiteSetting;
use App\Filament\Resources\SiteSettings\Schemas\SiteSettingForm;
use App\Filament\Resources\SiteSettings\Tables\SiteSettingsTable;
use App\Models\SiteSetting;
use App\Support\SiteSettingCatalog;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteSettingResource extends AdminResource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?int $navigationSort = 100;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('key', array_keys(SiteSettingCatalog::FIELDS));
    }

    public static function getRecordTitle(?Model $record): ?string
    {
        return $record ? SiteSettingCatalog::label($record) : static::getModelLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return SiteSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'view' => ViewSiteSetting::route('/{record}'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
    }
}

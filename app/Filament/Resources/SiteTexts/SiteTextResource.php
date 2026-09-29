<?php

namespace App\Filament\Resources\SiteTexts;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\SiteTexts\Pages\EditSiteText;
use App\Filament\Resources\SiteTexts\Pages\ListSiteTexts;
use App\Filament\Resources\SiteTexts\Pages\ViewSiteText;
use App\Filament\Resources\SiteTexts\Schemas\SiteTextForm;
use App\Filament\Resources\SiteTexts\Tables\SiteTextsTable;
use App\Models\SiteText;
use App\Support\SiteTextCatalog;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SiteTextResource extends AdminResource
{
    protected static ?string $model = SiteText::class;

    protected static ?int $navigationSort = 60;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function getRecordTitle(?Model $record): string
    {
        return $record ? SiteTextCatalog::label($record) : static::getModelLabel();
    }

    public static function form(Schema $schema): Schema
    {
        return SiteTextForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SiteTextsTable::configure($table);
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
            'index' => ListSiteTexts::route('/'),
            'view' => ViewSiteText::route('/{record}'),
            'edit' => EditSiteText::route('/{record}/edit'),
        ];
    }
}

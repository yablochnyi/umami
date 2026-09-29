<?php

namespace App\Filament\Resources\GalleryImages;

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\GalleryImages\Pages\CreateGalleryImage;
use App\Filament\Resources\GalleryImages\Pages\EditGalleryImage;
use App\Filament\Resources\GalleryImages\Pages\ListGalleryImages;
use App\Filament\Resources\GalleryImages\Pages\ViewGalleryImage;
use App\Filament\Resources\GalleryImages\Schemas\GalleryImageForm;
use App\Filament\Resources\GalleryImages\Tables\GalleryImagesTable;
use App\Models\GalleryImage;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GalleryImageResource extends AdminResource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?int $navigationSort = 50;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    public static function form(Schema $schema): Schema
    {
        return GalleryImageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryImagesTable::configure($table);
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
            'index' => ListGalleryImages::route('/'),
            'create' => CreateGalleryImage::route('/create'),
            'view' => ViewGalleryImage::route('/{record}'),
            'edit' => EditGalleryImage::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Models\SiteSetting;
use App\Support\SiteSettingCatalog as Catalog;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components(fn (?SiteSetting $record): array => $record ? [
            Section::make(Catalog::label($record))
                ->description(__('settings.hints.'.$record->key))
                ->schema(self::fields($record)),
        ] : []);
    }

    private static function fields(SiteSetting $record): array
    {
        $type = Catalog::type($record);
        $label = Catalog::label($record);

        if (in_array($type, ['image', 'video'], true)) {
            $limit = Catalog::uploadLimit($type);
            $upload = FileUpload::make('value')->label($label)
                ->disk('public')->directory('umami/settings')->visibility('public')
                ->acceptedFileTypes($type === 'video' ? ['video/mp4', 'video/webm', 'video/quicktime'] : ['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize($limit)->helperText(__('settings.formats.'.$type, ['size' => round($limit / 1024, 1)]));
            if ($type === 'image') {
                $upload->image()->imagePreviewHeight('240');
            }

            return [
                View::make('filament.partials.setting-video')
                    ->viewData(['setting' => $record])
                    ->visible($type === 'video' && filled($record->value)),
                $upload,
            ];
        }

        $input = TextInput::make('value')->label($label)->maxLength(2048);

        return [match ($type) {
            'url' => $input->url()->rules(['nullable', 'url:http,https']),
            'phone' => $input->tel()->maxLength(50),
            'call' => $input->tel()->maxLength(30)
                ->formatStateUsing(fn (?string $state) => preg_replace('/^tel:/', '', $state ?? ''))
                ->regex('/^\+?[0-9 ()-]{6,30}$/')
                ->dehydrateStateUsing(fn (?string $state) => filled($state) ? 'tel:'.preg_replace('/[ ()-]/', '', $state) : null),
            'analytics' => $input->placeholder('G-XXXXXXXXXX')->regex('/^G-[A-Z0-9]+$/')->maxLength(32),
            default => $input->maxLength(255),
        }];
    }
}

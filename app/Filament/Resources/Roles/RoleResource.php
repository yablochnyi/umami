<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\AdminResource;
use App\Models\Role;
use App\Support\AdminPermissions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleResource extends AdminResource
{
    protected static ?string $model = Role::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('admin.role_name'))->required()->maxLength(100)->unique(ignoreRecord: true)->columnSpanFull(),
            Section::make(__('admin.permissions'))->columnSpanFull()->schema(array_map(
                fn (string $key) => CheckboxList::make('permission_groups.'.$key)
                    ->label(__('admin.resources.'.$key))
                    ->options(collect(AdminPermissions::actions($key))->mapWithKeys(fn ($action) => [$action => __('admin.actions.'.$action)]))
                    ->columns(['default' => 2, 'lg' => 4])->gridDirection('row'),
                array_keys(AdminPermissions::RESOURCES),
            )),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('admin.role_name'))->formatStateUsing(fn (Role $record) => $record->display_name)->searchable(),
            TextColumn::make('users_count')->counts('users')->label(__('admin.resources.users')),
            TextColumn::make('is_admin')->label(__('admin.access'))->badge()
                ->formatStateUsing(fn (bool $state) => __($state ? 'admin.full_access' : 'admin.custom_access'))
                ->color(fn (bool $state) => $state ? 'primary' : 'gray'),
        ])->recordActions([ViewAction::make(), EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}

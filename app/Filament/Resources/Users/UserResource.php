<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\AdminResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;

class UserResource extends AdminResource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 80;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label(__('admin.user_name'))->required()->maxLength(255),
            TextInput::make('email')->label('Email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('password')->label(__('admin.password'))->password()->revealable()
                ->autocomplete('new-password')->rule(Password::min(12))
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->afterStateHydrated(fn (TextInput $component) => $component->state(null)),
            Select::make('role_id')->label(__('admin.singular.roles'))
                ->options(fn () => Role::query()->get()->mapWithKeys(fn (Role $role) => [$role->id => $role->display_name]))
                ->exists('roles', 'id')->searchable()->preload()
                ->disabled(fn (?User $record) => $record?->id === auth()->id()),
            Select::make('admin_locale')->label(__('admin.language'))->options(['pl' => 'Polski', 'uk' => 'Українська'])->default('pl')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label(__('admin.user_name'))->searchable(),
            TextColumn::make('email')->label('Email')->searchable(),
            TextColumn::make('role.name')->label(__('admin.singular.roles'))->badge()
                ->formatStateUsing(fn (User $record) => $record->role?->display_name)->placeholder(__('admin.no_access')),
            TextColumn::make('admin_locale')->label(__('admin.language'))->formatStateUsing(fn ($state) => $state === 'uk' ? 'Українська' : 'Polski'),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListUsers::route('/'), 'create' => Pages\CreateUser::route('/create'), 'edit' => Pages\EditUser::route('/{record}/edit')];
    }
}

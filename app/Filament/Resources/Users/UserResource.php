<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::USERS);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.user.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.user.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'playerProfile', 'playerProfile.primaryTeam']);
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UserSeasonConfigsRelationManager::class,
            RelationManagers\PlayerProfilesRelationManager::class,
            RelationManagers\ParentsRelationManager::class,
            RelationManagers\ChildrenRelationManager::class,
            RelationManagers\ConsentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}

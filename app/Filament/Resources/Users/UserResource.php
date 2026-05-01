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

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::USERS);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.users_and_people');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.user.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.user.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        UserDebug::log('UserResource: getEloquentQuery start');
        $query = parent::getEloquentQuery()
            ->with(['roles', 'playerProfile', 'playerProfile.primaryTeam']);
        UserDebug::log('UserResource: getEloquentQuery end');

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        UserDebug::log('UserResource: form configuration start');
        $result = UserForm::configure($schema);
        UserDebug::log('UserResource: form configuration end');
        return $result;
    }

    public static function table(Table $table): Table
    {
        UserDebug::log('UserResource: table configuration start');
        $result = UsersTable::configure($table);
        UserDebug::log('UserResource: table configuration end');
        return $result;
    }

    public static function getRelations(): array
    {
        UserDebug::log('UserResource: getRelations');
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

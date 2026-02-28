<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ListRoles;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::ROLES);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.role.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.role.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název role')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('users_count')
                    ->label('Počet uživatelů')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('permissions.name')
                    ->label('Oprávnění')
                    ->badge()
                    ->color('gray')
                    ->separator(', '),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
        ];
    }
}

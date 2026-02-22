<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\Permissions\Pages\ListPermissions;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    public static function getNavigationIcon(): ?string
    {
        return 'fa-light-key';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.permission.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.permission.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název oprávnění')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Přiřazeno rolím')
                    ->badge()
                    ->color('success')
                    ->separator(', '),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
        ];
    }
}

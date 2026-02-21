<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-shield-check';

    protected static null|string|\UnitEnum $navigationGroup = 'Správa uživatelů';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Role';

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

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

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-lock-closed';

    protected static null|string|\UnitEnum $navigationGroup = 'Správa uživatelů';

    protected static ?string $modelLabel = 'Oprávnění';

    protected static ?string $pluralModelLabel = 'Oprávnění';

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

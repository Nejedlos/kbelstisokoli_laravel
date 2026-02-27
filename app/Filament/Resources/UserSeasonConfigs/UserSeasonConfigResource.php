<?php

namespace App\Filament\Resources\UserSeasonConfigs;

use App\Filament\Resources\UserSeasonConfigs\Pages\CreateUserSeasonConfig;
use App\Filament\Resources\UserSeasonConfigs\Pages\EditUserSeasonConfig;
use App\Filament\Resources\UserSeasonConfigs\Pages\ListUserSeasonConfigs;
use App\Filament\Resources\UserSeasonConfigs\Schemas\UserSeasonConfigForm;
use App\Filament\Resources\UserSeasonConfigs\Tables\UserSeasonConfigsTable;
use App\Models\UserSeasonConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserSeasonConfigResource extends Resource
{
    protected static ?string $model = UserSeasonConfig::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return 'Sezónní konfigurace uživatele';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Sezónní konfigurace uživatelů';
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::USER_GEAR);
    }

    public static function getNavigationSort(): ?int
    {
        return 11;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\UserSeasonConfigs\Schemas\UserSeasonConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\UserSeasonConfigs\Tables\UserSeasonConfigsTable::configure($table);
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
            'index' => ListUserSeasonConfigs::route('/'),
            'create' => CreateUserSeasonConfig::route('/create'),
            'edit' => EditUserSeasonConfig::route('/{record}/edit'),
        ];
    }
}

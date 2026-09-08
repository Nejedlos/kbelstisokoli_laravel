<?php

namespace App\Filament\Resources\UserSeasonConfigs;

use App\Filament\Resources\UserSeasonConfigs\Pages\CreateUserSeasonConfig;
use App\Filament\Resources\UserSeasonConfigs\Pages\EditUserSeasonConfig;
use App\Filament\Resources\UserSeasonConfigs\Pages\ListUserSeasonConfigs;
use App\Filament\Resources\UserSeasonConfigs\Schemas\UserSeasonConfigForm;
use App\Filament\Resources\UserSeasonConfigs\Tables\UserSeasonConfigsTable;
use App\Models\UserSeasonConfig;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class UserSeasonConfigResource extends Resource
{
    protected static ?string $model = UserSeasonConfig::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.users_and_people');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.user_season_config.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.user_season_config.plural_label');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return IconHelper::get(IconHelper::USER_GEAR);
    }

    public static function getNavigationSort(): ?int
    {
        return 40;
    }

    public static function form(Schema $schema): Schema
    {
        return UserSeasonConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserSeasonConfigsTable::configure($table);
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

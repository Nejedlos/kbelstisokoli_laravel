<?php

namespace App\Filament\Resources\PlayerProfiles;

use App\Filament\Resources\PlayerProfiles\Pages\CreatePlayerProfile;
use App\Filament\Resources\PlayerProfiles\Pages\EditPlayerProfile;
use App\Filament\Resources\PlayerProfiles\Pages\ListPlayerProfiles;
use App\Filament\Resources\PlayerProfiles\Schemas\PlayerProfileForm;
use App\Filament\Resources\PlayerProfiles\Tables\PlayerProfilesTable;
use App\Models\PlayerProfile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PlayerProfileResource extends Resource
{
    protected static ?string $model = PlayerProfile::class;

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::PLAYER_PROFILES);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.player_profile.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.player_profile.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return PlayerProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlayerProfilesTable::configure($table);
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
            'index' => ListPlayerProfiles::route('/'),
            'create' => CreatePlayerProfile::route('/create'),
            'edit' => EditPlayerProfile::route('/{record}/edit'),
        ];
    }
}

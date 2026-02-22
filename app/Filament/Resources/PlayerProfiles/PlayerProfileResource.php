<?php

namespace App\Filament\Resources\PlayerProfiles;

use App\Filament\Resources\PlayerProfiles\Pages\CreatePlayerProfile;
use App\Filament\Resources\PlayerProfiles\Pages\EditPlayerProfile;
use App\Filament\Resources\PlayerProfiles\Pages\ListPlayerProfiles;
use App\Filament\Resources\PlayerProfiles\Schemas\PlayerProfileForm;
use App\Filament\Resources\PlayerProfiles\Tables\PlayerProfilesTable;
use App\Models\PlayerProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlayerProfileResource extends Resource
{
    protected static ?string $model = PlayerProfile::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-user-circle';

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

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

    protected static null|string|\UnitEnum $navigationGroup = 'Správa uživatelů';

    protected static ?string $modelLabel = 'Hráčský profil';

    protected static ?string $pluralModelLabel = 'Hráčské profily';

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

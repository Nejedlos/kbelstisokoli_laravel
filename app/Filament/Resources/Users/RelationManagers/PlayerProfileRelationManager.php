<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\PlayerProfiles\Schemas\PlayerProfileForm;
use App\Filament\Resources\PlayerProfiles\Tables\PlayerProfilesTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PlayerProfileRelationManager extends RelationManager
{
    protected static string $relationship = 'playerProfile';

    protected static ?string $title = 'Hráčský profil';

    protected static ?string $modelLabel = 'Hráčský profil';

    protected static ?string $pluralModelLabel = 'Hráčské profily';

    public function form(Schema $schema): Schema
    {
        return PlayerProfileForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return PlayerProfilesTable::configure($table);
    }
}

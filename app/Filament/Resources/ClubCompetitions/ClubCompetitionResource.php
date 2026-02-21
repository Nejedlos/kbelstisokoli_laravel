<?php

namespace App\Filament\Resources\ClubCompetitions;

use App\Filament\Resources\ClubCompetitions\Pages\CreateClubCompetition;
use App\Filament\Resources\ClubCompetitions\Pages\EditClubCompetition;
use App\Filament\Resources\ClubCompetitions\Pages\ListClubCompetitions;
use App\Filament\Resources\ClubCompetitions\Schemas\ClubCompetitionForm;
use App\Filament\Resources\ClubCompetitions\Tables\ClubCompetitionsTable;
use App\Models\ClubCompetition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClubCompetitionResource extends Resource
{
    protected static ?string $model = ClubCompetition::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-trophy';

    protected static null|string|\UnitEnum $navigationGroup = 'Statistiky';

    protected static ?string $modelLabel = 'Klubová soutěž';

    protected static ?string $pluralModelLabel = 'Klubové soutěže';

    public static function form(Schema $schema): Schema
    {
        return ClubCompetitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClubCompetitionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubCompetitions::route('/'),
            'create' => CreateClubCompetition::route('/create'),
            'edit' => EditClubCompetition::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\ClubCompetitions;

use App\Filament\Resources\ClubCompetitions\Pages\CreateClubCompetition;
use App\Filament\Resources\ClubCompetitions\Pages\EditClubCompetition;
use App\Filament\Resources\ClubCompetitions\Pages\ListClubCompetitions;
use App\Filament\Resources\ClubCompetitions\Schemas\ClubCompetitionForm;
use App\Filament\Resources\ClubCompetitions\Tables\ClubCompetitionsTable;
use App\Models\ClubCompetition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ClubCompetitionResource extends Resource
{
    protected static ?string $model = ClubCompetition::class;

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::COMPETITIONS);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.statistics');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.club_competition.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.club_competition.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

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

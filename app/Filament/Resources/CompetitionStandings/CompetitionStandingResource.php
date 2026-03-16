<?php

namespace App\Filament\Resources\CompetitionStandings;

use App\Filament\Resources\CompetitionStandings\Pages\CreateCompetitionStanding;
use App\Filament\Resources\CompetitionStandings\Pages\EditCompetitionStanding;
use App\Filament\Resources\CompetitionStandings\Pages\ListCompetitionStandings;
use App\Filament\Resources\CompetitionStandings\Schemas\CompetitionStandingForm;
use App\Filament\Resources\CompetitionStandings\Tables\CompetitionStandingsTable;
use App\Models\CompetitionStanding;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CompetitionStandingResource extends Resource
{
    protected static ?string $model = CompetitionStanding::class;

    public static function getNavigationSort(): ?int
    {
        return 20;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.competition_standing.plural_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.competition_standing.plural_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.competition_standing.label');
    }

    public static function getNavigationIcon(): string
    {
        return FilamentIcon::get(AppIcon::TABLE);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.statistics_and_data');
    }

    public static function form(Schema $schema): Schema
    {
        return CompetitionStandingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompetitionStandingsTable::configure($table);
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
            'index' => ListCompetitionStandings::route('/'),
            'create' => CreateCompetitionStanding::route('/create'),
            'edit' => EditCompetitionStanding::route('/{record}/edit'),
        ];
    }
}

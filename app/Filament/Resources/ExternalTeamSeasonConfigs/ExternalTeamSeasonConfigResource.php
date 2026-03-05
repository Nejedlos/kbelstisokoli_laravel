<?php

namespace App\Filament\Resources\ExternalTeamSeasonConfigs;

use App\Filament\Resources\ExternalTeamSeasonConfigs\Pages\CreateExternalTeamSeasonConfig;
use App\Filament\Resources\ExternalTeamSeasonConfigs\Pages\EditExternalTeamSeasonConfig;
use App\Filament\Resources\ExternalTeamSeasonConfigs\Pages\ListExternalTeamSeasonConfigs;
use App\Filament\Resources\ExternalTeamSeasonConfigs\Schemas\ExternalTeamSeasonConfigForm;
use App\Filament\Resources\ExternalTeamSeasonConfigs\Tables\ExternalTeamSeasonConfigsTable;
use App\Models\ExternalTeamSeasonConfig;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ExternalTeamSeasonConfigResource extends Resource
{
    protected static ?string $model = ExternalTeamSeasonConfig::class;

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        return 'Konfigurace sezón';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Konfigurace sezón';
    }

    public static function getModelLabel(): string
    {
        return 'Konfigurace sezóny';
    }

    public static function getNavigationIcon(): string
    {
        return FilamentIcon::get(AppIcon::STAT_SOURCES);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Externí statistiky';
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalTeamSeasonConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalTeamSeasonConfigsTable::configure($table);
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
            'index' => ListExternalTeamSeasonConfigs::route('/'),
            'create' => CreateExternalTeamSeasonConfig::route('/create'),
            'edit' => EditExternalTeamSeasonConfig::route('/{record}/edit'),
        ];
    }
}

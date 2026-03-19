<?php

namespace App\Filament\Resources\ExternalTeamMappings;

use App\Filament\Resources\ExternalTeamMappings\Pages\CreateExternalTeamMapping;
use App\Filament\Resources\ExternalTeamMappings\Pages\EditExternalTeamMapping;
use App\Filament\Resources\ExternalTeamMappings\Pages\ListExternalTeamMappings;
use App\Filament\Resources\ExternalTeamMappings\Schemas\ExternalTeamMappingForm;
use App\Filament\Resources\ExternalTeamMappings\Tables\ExternalTeamMappingsTable;
use App\Models\ExternalTeamMapping;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ExternalTeamMappingResource extends Resource
{
    protected static ?string $model = ExternalTeamMapping::class;

    public static function getNavigationSort(): ?int
    {
        return 32;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.external_team_mapping.plural_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.external_team_mapping.plural_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.external_team_mapping.label');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return FilamentIcon::get(AppIcon::TABLE);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.statistics_and_data') . ' > ' . __('admin.navigation.groups.external_data');
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalTeamMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalTeamMappingsTable::configure($table);
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
            'index' => ListExternalTeamMappings::route('/'),
            'create' => CreateExternalTeamMapping::route('/create'),
            'edit' => EditExternalTeamMapping::route('/{record}/edit'),
        ];
    }
}

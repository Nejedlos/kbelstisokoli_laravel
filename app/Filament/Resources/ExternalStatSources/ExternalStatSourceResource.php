<?php

namespace App\Filament\Resources\ExternalStatSources;

use App\Filament\Resources\ExternalStatSources\Pages\CreateExternalStatSource;
use App\Filament\Resources\ExternalStatSources\Pages\EditExternalStatSource;
use App\Filament\Resources\ExternalStatSources\Pages\ListExternalStatSources;
use App\Filament\Resources\ExternalStatSources\Schemas\ExternalStatSourceForm;
use App\Filament\Resources\ExternalStatSources\Tables\ExternalStatSourcesTable;
use App\Models\ExternalStatSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExternalStatSourceResource extends Resource
{
    protected static ?string $model = ExternalStatSource::class;

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::STAT_SOURCES);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.statistics');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.external_stat_source.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.external_stat_source.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalStatSourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalStatSourcesTable::configure($table);
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
            'index' => ListExternalStatSources::route('/'),
            'create' => CreateExternalStatSource::route('/create'),
            'edit' => EditExternalStatSource::route('/{record}/edit'),
        ];
    }
}

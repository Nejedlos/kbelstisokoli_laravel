<?php

namespace App\Filament\Resources\ExternalImportRuns;

use App\Filament\Resources\ExternalImportRuns\Pages\CreateExternalImportRun;
use App\Filament\Resources\ExternalImportRuns\Pages\EditExternalImportRun;
use App\Filament\Resources\ExternalImportRuns\Pages\ListExternalImportRuns;
use App\Filament\Resources\ExternalImportRuns\Schemas\ExternalImportRunForm;
use App\Filament\Resources\ExternalImportRuns\Tables\ExternalImportRunsTable;
use App\Models\ExternalImportRun;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ExternalImportRunResource extends Resource
{
    protected static ?string $model = ExternalImportRun::class;

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationLabel(): string
    {
        return 'Historie importů';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Historie importů';
    }

    public static function getModelLabel(): string
    {
        return 'Běh importu';
    }

    public static function getNavigationIcon(): string
    {
        return FilamentIcon::get(AppIcon::CRON_LOGS);
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Externí statistiky';
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalImportRunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalImportRunsTable::configure($table);
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
            'index' => ListExternalImportRuns::route('/'),
            'create' => CreateExternalImportRun::route('/create'),
            'edit' => EditExternalImportRun::route('/{record}/edit'),
        ];
    }
}

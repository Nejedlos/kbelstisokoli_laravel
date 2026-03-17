<?php

namespace App\Filament\Resources\ExternalImportRuns;

use App\Filament\Resources\ExternalImportRuns\Pages\CreateExternalImportRun;
use App\Filament\Resources\ExternalImportRuns\Pages\EditExternalImportRun;
use App\Filament\Resources\ExternalImportRuns\Pages\ListExternalImportRuns;
use App\Filament\Resources\ExternalImportRuns\Pages\ViewExternalImportRun;
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

    protected static ?string $slug = 'external-import-runs';

    public static function getNavigationSort(): ?int
    {
        return 35;
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

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return FilamentIcon::get(AppIcon::CRON_LOGS);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.statistics_and_data') . ' > ' . __('admin.navigation.groups.external_data');
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
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExternalImportRuns::route('/'),
            'create' => CreateExternalImportRun::route('/create'),
            'view' => ViewExternalImportRun::route('/{record}'),
            'edit' => EditExternalImportRun::route('/{record}/edit'),
        ];
    }
}

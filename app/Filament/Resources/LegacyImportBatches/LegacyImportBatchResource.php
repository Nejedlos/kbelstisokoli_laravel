<?php

namespace App\Filament\Resources\LegacyImportBatches;

use App\Filament\Resources\LegacyImportBatches\Schemas\LegacyImportBatchForm;
use App\Filament\Resources\LegacyImportBatches\Tables\LegacyImportBatchesTable;
use App\Models\LegacyImportBatch;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LegacyImportBatchResource extends Resource
{
    protected static ?string $model = LegacyImportBatch::class;

    public static function getNavigationLabel(): string
    {
        return 'Import historických dat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Importy historických dat';
    }

    public static function getModelLabel(): string
    {
        return 'Historický import';
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return FilamentIcon::get(AppIcon::UPLOAD);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.statistics_and_data') . ' > ' . __('admin.navigation.groups.external_data');
    }

    public static function getNavigationSort(): ?int
    {
        return 36;
    }

    public static function form(Schema $schema): Schema
    {
        return LegacyImportBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LegacyImportBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegacyImportBatches::route('/'),
            'create' => Pages\CreateLegacyImportBatch::route('/create'),
            'view' => Pages\ViewLegacyImportBatch::route('/{record}'),
            'edit' => Pages\EditLegacyImportBatch::route('/{record}/edit'),
        ];
    }
}

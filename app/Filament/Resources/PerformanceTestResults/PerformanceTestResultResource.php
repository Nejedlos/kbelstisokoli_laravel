<?php

namespace App\Filament\Resources\PerformanceTestResults;

use App\Filament\Resources\PerformanceTestResults\Pages\CreatePerformanceTestResult;
use App\Filament\Resources\PerformanceTestResults\Pages\EditPerformanceTestResult;
use App\Filament\Resources\PerformanceTestResults\Pages\ListPerformanceTestResults;
use App\Filament\Resources\PerformanceTestResults\Schemas\PerformanceTestResultForm;
use App\Filament\Resources\PerformanceTestResults\Tables\PerformanceTestResultsTable;
use App\Models\PerformanceTestResult;
use BackedEnum;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PerformanceTestResultResource extends Resource
{
    protected static ?string $model = PerformanceTestResult::class;

    public static function getNavigationIcon(): ?string
    {
        return IconHelper::get(\App\Support\Icons\AppIcon::GAUGE);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getModelLabel(): string
    {
        return 'Test výkonu';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Testy výkonu';
    }

    public static function form(Schema $schema): Schema
    {
        return PerformanceTestResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerformanceTestResultsTable::configure($table);
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
            'index' => ListPerformanceTestResults::route('/'),
            'create' => CreatePerformanceTestResult::route('/create'),
            'edit' => EditPerformanceTestResult::route('/{record}/edit'),
        ];
    }
}

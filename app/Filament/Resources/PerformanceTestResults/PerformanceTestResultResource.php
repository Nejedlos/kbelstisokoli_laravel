<?php

namespace App\Filament\Resources\PerformanceTestResults;

use App\Filament\Resources\PerformanceTestResults\Pages\CreatePerformanceTestResult;
use App\Filament\Resources\PerformanceTestResults\Pages\EditPerformanceTestResult;
use App\Filament\Resources\PerformanceTestResults\Pages\ListPerformanceTestResults;
use App\Filament\Resources\PerformanceTestResults\Schemas\PerformanceTestResultForm;
use App\Filament\Resources\PerformanceTestResults\Tables\PerformanceTestResultsTable;
use App\Models\PerformanceTestResult;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PerformanceTestResultResource extends Resource
{
    protected static ?string $model = PerformanceTestResult::class;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return IconHelper::get(\App\Support\Icons\AppIcon::GAUGE);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getNavigationSort(): ?int
    {
        return 60;
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.performance_test_result.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.performance_test_result.plural_label');
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

    public static function getWidgets(): array
    {
        return [
            Widgets\PerformanceComparisonWidget::class,
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

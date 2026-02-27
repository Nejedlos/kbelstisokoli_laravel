<?php

namespace App\Filament\Resources\PerformanceTestResults;

use App\Filament\Resources\PerformanceTestResults\Pages\CreatePerformanceTestResult;
use App\Filament\Resources\PerformanceTestResults\Pages\EditPerformanceTestResult;
use App\Filament\Resources\PerformanceTestResults\Pages\ListPerformanceTestResults;
use App\Filament\Resources\PerformanceTestResults\Schemas\PerformanceTestResultForm;
use App\Filament\Resources\PerformanceTestResults\Tables\PerformanceTestResultsTable;
use App\Models\PerformanceTestResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PerformanceTestResultResource extends Resource
{
    protected static ?string $model = PerformanceTestResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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

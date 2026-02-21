<?php

namespace App\Filament\Resources\StatisticSets;

use App\Filament\Resources\StatisticSets\Pages\CreateStatisticSet;
use App\Filament\Resources\StatisticSets\Pages\EditStatisticSet;
use App\Filament\Resources\StatisticSets\Pages\ListStatisticSets;
use App\Filament\Resources\StatisticSets\Schemas\StatisticSetForm;
use App\Filament\Resources\StatisticSets\Tables\StatisticSetsTable;
use App\Models\StatisticSet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StatisticSetResource extends Resource
{
    protected static ?string $model = StatisticSet::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static null|string|\UnitEnum $navigationGroup = 'Statistiky';

    protected static ?string $modelLabel = 'Sada statistik';

    protected static ?string $pluralModelLabel = 'Sady statistik';

    public static function form(Schema $schema): Schema
    {
        return StatisticSetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatisticSetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RowsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatisticSets::route('/'),
            'create' => CreateStatisticSet::route('/create'),
            'edit' => EditStatisticSet::route('/{record}/edit'),
        ];
    }
}

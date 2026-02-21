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

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-cloud-arrow-down';

    protected static null|string|\UnitEnum $navigationGroup = 'Statistiky';

    protected static ?string $modelLabel = 'Externí zdroj';

    protected static ?string $pluralModelLabel = 'Externí zdroje';

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

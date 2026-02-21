<?php

namespace App\Filament\Resources\Opponents;

use App\Filament\Resources\Opponents\Pages\CreateOpponent;
use App\Filament\Resources\Opponents\Pages\EditOpponent;
use App\Filament\Resources\Opponents\Pages\ListOpponents;
use App\Filament\Resources\Opponents\Schemas\OpponentForm;
use App\Filament\Resources\Opponents\Tables\OpponentsTable;
use App\Models\Opponent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpponentResource extends Resource
{
    protected static ?string $model = Opponent::class;

    protected static null|string|\UnitEnum $navigationGroup = 'Sportovní agenda';

    protected static ?string $modelLabel = 'Soupeř';

    protected static ?string $pluralModelLabel = 'Soupeři';

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Schema $schema): Schema
    {
        return OpponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpponentsTable::configure($table);
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
            'index' => ListOpponents::route('/'),
            'create' => CreateOpponent::route('/create'),
            'edit' => EditOpponent::route('/{record}/edit'),
        ];
    }
}

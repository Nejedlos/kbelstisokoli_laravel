<?php

namespace App\Filament\Resources\BasketballMatches;

use App\Filament\Resources\BasketballMatches\Pages\CreateBasketballMatch;
use App\Filament\Resources\BasketballMatches\Pages\EditBasketballMatch;
use App\Filament\Resources\BasketballMatches\Pages\ListBasketballMatches;
use App\Filament\Resources\BasketballMatches\Schemas\BasketballMatchForm;
use App\Filament\Resources\BasketballMatches\Tables\BasketballMatchesTable;
use App\Models\BasketballMatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BasketballMatchResource extends Resource
{
    protected static ?string $model = BasketballMatch::class;

    protected static null|string|\UnitEnum $navigationGroup = 'Sportovní agenda';

    protected static ?string $modelLabel = 'Zápas';

    protected static ?string $pluralModelLabel = 'Zápasy';

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-trophy';

    public static function form(Schema $schema): Schema
    {
        return BasketballMatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BasketballMatchesTable::configure($table);
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
            'index' => ListBasketballMatches::route('/'),
            'create' => CreateBasketballMatch::route('/create'),
            'edit' => EditBasketballMatch::route('/{record}/edit'),
        ];
    }
}

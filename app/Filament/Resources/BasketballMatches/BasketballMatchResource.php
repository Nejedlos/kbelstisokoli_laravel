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

use App\Filament\Resources\ClubEvents\RelationManagers\AttendancesRelationManager;

class BasketballMatchResource extends Resource
{
    protected static ?string $model = BasketballMatch::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.sports_agenda');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.basketball_match.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.basketball_match.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::MATCHES);
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

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
            AttendancesRelationManager::class,
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

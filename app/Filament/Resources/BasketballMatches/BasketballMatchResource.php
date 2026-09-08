<?php

namespace App\Filament\Resources\BasketballMatches;

use App\Filament\Resources\BasketballMatches\Pages\CreateBasketballMatch;
use App\Filament\Resources\BasketballMatches\Pages\EditBasketballMatch;
use App\Filament\Resources\BasketballMatches\Pages\ListBasketballMatches;
use App\Filament\Resources\BasketballMatches\Schemas\BasketballMatchForm;
use App\Filament\Resources\BasketballMatches\Tables\BasketballMatchesTable;
use App\Filament\Resources\ClubEvents\RelationManagers\AttendancesRelationManager;
use App\Models\BasketballMatch;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class BasketballMatchResource extends Resource
{
    protected static ?string $model = BasketballMatch::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.sports_agenda');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.basketball_match.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.basketball_match.plural_label');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return IconHelper::get(IconHelper::MATCHES);
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return []; // Vypnuto kvůli relacím na translatable pole a kompatibilitě s Webglobe (json_unquote)
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['teams', 'opponent', 'season'])
            ->withCount(['mismatches']);
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

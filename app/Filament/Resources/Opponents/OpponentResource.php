<?php

namespace App\Filament\Resources\Opponents;

use App\Filament\Resources\Opponents\Pages\CreateOpponent;
use App\Filament\Resources\Opponents\Pages\EditOpponent;
use App\Filament\Resources\Opponents\Pages\ListOpponents;
use App\Filament\Resources\Opponents\Schemas\OpponentForm;
use App\Filament\Resources\Opponents\Tables\OpponentsTable;
use App\Models\Opponent;
use App\Filament\Resources\Opponents\Widgets\OpponentMergeSuggestionsWidget;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OpponentResource extends Resource
{
    protected static ?string $model = Opponent::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.sports_agenda');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.opponent.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.opponent.plural_label');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::OPPONENTS);
    }

    public static function getNavigationSort(): ?int
    {
        return 70;
    }

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

    public static function getWidgets(): array
    {
        return [
            OpponentMergeSuggestionsWidget::class,
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

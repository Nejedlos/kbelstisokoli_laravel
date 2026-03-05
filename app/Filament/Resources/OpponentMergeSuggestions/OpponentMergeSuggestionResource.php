<?php

namespace App\Filament\Resources\OpponentMergeSuggestions;

use App\Filament\Resources\OpponentMergeSuggestions\Pages\CreateOpponentMergeSuggestion;
use App\Filament\Resources\OpponentMergeSuggestions\Pages\EditOpponentMergeSuggestion;
use App\Filament\Resources\OpponentMergeSuggestions\Pages\ListOpponentMergeSuggestions;
use App\Filament\Resources\OpponentMergeSuggestions\Schemas\OpponentMergeSuggestionForm;
use App\Filament\Resources\OpponentMergeSuggestions\Tables\OpponentMergeSuggestionsTable;
use App\Models\OpponentMergeSuggestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OpponentMergeSuggestionResource extends Resource
{
    protected static ?string $model = OpponentMergeSuggestion::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.sports_agenda');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-arrows-pointing-in';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.opponent_merge_suggestion.plural_label');
    }

    public static function getPluralLabel(): string
    {
        return __('admin.resources.opponent_merge_suggestion.plural_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.opponent_merge_suggestion.label');
    }

    public static function getNavigationSort(): ?int
    {
        return 51;
    }

    public static function form(Schema $schema): Schema
    {
        return OpponentMergeSuggestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpponentMergeSuggestionsTable::configure($table);
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
            'index' => ListOpponentMergeSuggestions::route('/'),
        ];
    }
}

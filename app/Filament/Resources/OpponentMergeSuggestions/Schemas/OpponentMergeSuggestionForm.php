<?php

namespace App\Filament\Resources\OpponentMergeSuggestions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OpponentMergeSuggestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sourceOpponent.name')
                    ->label(__('admin.navigation.resources.opponent_merge_suggestion.fields.source_opponent'))
                    ->disabled(),
                TextInput::make('targetOpponent.name')
                    ->label(__('admin.navigation.resources.opponent_merge_suggestion.fields.target_opponent'))
                    ->disabled(),
                TextInput::make('similarity')
                    ->label(__('admin.navigation.resources.opponent_merge_suggestion.fields.similarity'))
                    ->disabled(),
                TextInput::make('status')
                    ->label(__('admin.navigation.resources.opponent_merge_suggestion.fields.status'))
                    ->disabled(),
            ]);
    }
}

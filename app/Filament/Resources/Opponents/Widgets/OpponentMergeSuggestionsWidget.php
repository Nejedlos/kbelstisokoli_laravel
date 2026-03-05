<?php

namespace App\Filament\Resources\Opponents\Widgets;

use App\Models\OpponentMergeSuggestion;
use App\Filament\Resources\OpponentMergeSuggestions\Tables\OpponentMergeSuggestionsTable;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Table;

class OpponentMergeSuggestionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return OpponentMergeSuggestionsTable::configure($table)
            ->query(
                OpponentMergeSuggestion::query()
                    ->where('status', 'pending')
                    ->latest()
            )
            ->heading(__('admin.navigation.resources.opponent_merge_suggestion.plural_label'))
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }

    public static function canView(): bool
    {
        return OpponentMergeSuggestion::where('status', 'pending')->exists();
    }
}

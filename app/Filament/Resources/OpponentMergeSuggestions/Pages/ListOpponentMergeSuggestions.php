<?php

namespace App\Filament\Resources\OpponentMergeSuggestions\Pages;

use App\Filament\Resources\OpponentMergeSuggestions\OpponentMergeSuggestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpponentMergeSuggestions extends ListRecords
{
    protected static string $resource = OpponentMergeSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}

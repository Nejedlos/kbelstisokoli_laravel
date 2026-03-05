<?php

namespace App\Filament\Resources\OpponentMergeSuggestions\Pages;

use App\Filament\Resources\OpponentMergeSuggestions\OpponentMergeSuggestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpponentMergeSuggestion extends EditRecord
{
    protected static string $resource = OpponentMergeSuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Opponents\Pages;

use App\Filament\Resources\Opponents\OpponentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpponent extends EditRecord
{
    protected static string $resource = OpponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

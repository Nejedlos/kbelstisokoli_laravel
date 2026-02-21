<?php

namespace App\Filament\Resources\BasketballMatches\Pages;

use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBasketballMatch extends EditRecord
{
    protected static string $resource = BasketballMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

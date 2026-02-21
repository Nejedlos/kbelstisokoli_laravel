<?php

namespace App\Filament\Resources\BasketballMatches\Pages;

use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBasketballMatches extends ListRecords
{
    protected static string $resource = BasketballMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

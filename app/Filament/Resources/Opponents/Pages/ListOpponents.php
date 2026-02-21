<?php

namespace App\Filament\Resources\Opponents\Pages;

use App\Filament\Resources\Opponents\OpponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpponents extends ListRecords
{
    protected static string $resource = OpponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

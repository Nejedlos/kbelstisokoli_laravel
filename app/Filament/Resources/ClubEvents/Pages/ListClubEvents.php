<?php

namespace App\Filament\Resources\ClubEvents\Pages;

use App\Filament\Resources\ClubEvents\ClubEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClubEvents extends ListRecords
{
    protected static string $resource = ClubEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

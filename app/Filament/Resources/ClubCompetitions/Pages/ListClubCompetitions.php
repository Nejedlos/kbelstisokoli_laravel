<?php

namespace App\Filament\Resources\ClubCompetitions\Pages;

use App\Filament\Resources\ClubCompetitions\ClubCompetitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClubCompetitions extends ListRecords
{
    protected static string $resource = ClubCompetitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

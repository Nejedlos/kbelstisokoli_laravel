<?php

namespace App\Filament\Resources\CompetitionStandings\Pages;

use App\Filament\Resources\CompetitionStandings\CompetitionStandingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompetitionStandings extends ListRecords
{
    protected static string $resource = CompetitionStandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

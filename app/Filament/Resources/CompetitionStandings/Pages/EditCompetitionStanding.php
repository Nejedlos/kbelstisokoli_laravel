<?php

namespace App\Filament\Resources\CompetitionStandings\Pages;

use App\Filament\Resources\CompetitionStandings\CompetitionStandingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompetitionStanding extends EditRecord
{
    protected static string $resource = CompetitionStandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ClubCompetitions\Pages;

use App\Filament\Resources\ClubCompetitions\ClubCompetitionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClubCompetition extends EditRecord
{
    protected static string $resource = ClubCompetitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

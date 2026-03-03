<?php

namespace App\Filament\Resources\ExternalTeamSeasonConfigs\Pages;

use App\Filament\Resources\ExternalTeamSeasonConfigs\ExternalTeamSeasonConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalTeamSeasonConfigs extends ListRecords
{
    protected static string $resource = ExternalTeamSeasonConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

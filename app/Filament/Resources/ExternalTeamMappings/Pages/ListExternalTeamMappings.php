<?php

namespace App\Filament\Resources\ExternalTeamMappings\Pages;

use App\Filament\Resources\ExternalTeamMappings\ExternalTeamMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalTeamMappings extends ListRecords
{
    protected static string $resource = ExternalTeamMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

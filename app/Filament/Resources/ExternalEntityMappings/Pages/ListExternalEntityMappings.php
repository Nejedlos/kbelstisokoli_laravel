<?php

namespace App\Filament\Resources\ExternalEntityMappings\Pages;

use App\Filament\Resources\ExternalEntityMappings\ExternalEntityMappingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalEntityMappings extends ListRecords
{
    protected static string $resource = ExternalEntityMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

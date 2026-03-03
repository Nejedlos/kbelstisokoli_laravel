<?php

namespace App\Filament\Resources\ExternalTeamMappings\Pages;

use App\Filament\Resources\ExternalTeamMappings\ExternalTeamMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalTeamMapping extends EditRecord
{
    protected static string $resource = ExternalTeamMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ExternalEntityMappings\Pages;

use App\Filament\Resources\ExternalEntityMappings\ExternalEntityMappingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalEntityMapping extends EditRecord
{
    protected static string $resource = ExternalEntityMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

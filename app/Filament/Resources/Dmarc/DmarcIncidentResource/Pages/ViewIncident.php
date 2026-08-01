<?php

namespace App\Filament\Resources\Dmarc\DmarcIncidentResource\Pages;

use App\Filament\Resources\Dmarc\DmarcIncidentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIncident extends ViewRecord
{
    protected static string $resource = DmarcIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

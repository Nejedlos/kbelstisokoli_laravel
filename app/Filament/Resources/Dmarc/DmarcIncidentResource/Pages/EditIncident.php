<?php

namespace App\Filament\Resources\Dmarc\DmarcIncidentResource\Pages;

use App\Filament\Resources\Dmarc\DmarcIncidentResource;
use Filament\Resources\Pages\EditRecord;

class EditIncident extends EditRecord
{
    protected static string $resource = DmarcIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

<?php

namespace App\Filament\Resources\Dmarc\DmarcIncidentResource\Pages;

use App\Filament\Resources\Dmarc\DmarcIncidentResource;
use Filament\Resources\Pages\ListRecords;

class ListIncidents extends ListRecords
{
    protected static string $resource = DmarcIncidentResource::class;
}

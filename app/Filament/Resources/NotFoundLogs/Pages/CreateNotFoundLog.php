<?php

namespace App\Filament\Resources\NotFoundLogs\Pages;

use App\Filament\Resources\NotFoundLogs\NotFoundLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNotFoundLog extends CreateRecord
{
    protected static string $resource = NotFoundLogResource::class;
}

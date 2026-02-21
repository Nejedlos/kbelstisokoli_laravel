<?php

namespace App\Filament\Resources\CronLogs\Pages;

use App\Filament\Resources\CronLogs\CronLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCronLog extends CreateRecord
{
    protected static string $resource = CronLogResource::class;
}

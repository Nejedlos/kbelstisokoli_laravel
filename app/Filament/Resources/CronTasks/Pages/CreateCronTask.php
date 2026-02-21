<?php

namespace App\Filament\Resources\CronTasks\Pages;

use App\Filament\Resources\CronTasks\CronTaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCronTask extends CreateRecord
{
    protected static string $resource = CronTaskResource::class;
}

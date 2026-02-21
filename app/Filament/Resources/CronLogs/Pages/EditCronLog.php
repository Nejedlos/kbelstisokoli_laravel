<?php

namespace App\Filament\Resources\CronLogs\Pages;

use App\Filament\Resources\CronLogs\CronLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCronLog extends EditRecord
{
    protected static string $resource = CronLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

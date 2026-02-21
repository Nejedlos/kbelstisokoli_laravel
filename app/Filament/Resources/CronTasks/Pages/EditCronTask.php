<?php

namespace App\Filament\Resources\CronTasks\Pages;

use App\Filament\Resources\CronTasks\CronTaskResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCronTask extends EditRecord
{
    protected static string $resource = CronTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

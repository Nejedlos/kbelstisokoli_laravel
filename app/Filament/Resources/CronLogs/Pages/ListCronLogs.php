<?php

namespace App\Filament\Resources\CronLogs\Pages;

use App\Filament\Resources\CronLogs\CronLogResource;
use App\Filament\Resources\CronLogs\Widgets\CronHeartbeatWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCronLogs extends ListRecords
{
    protected static string $resource = CronLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CronHeartbeatWidget::class,
        ];
    }
}

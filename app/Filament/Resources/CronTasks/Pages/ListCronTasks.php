<?php

namespace App\Filament\Resources\CronTasks\Pages;

use App\Filament\Resources\CronTasks\CronTaskResource;
use App\Filament\Widgets\CronLogsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCronTasks extends ListRecords
{
    protected static string $resource = CronTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CronLogsTable::class,
        ];
    }
}

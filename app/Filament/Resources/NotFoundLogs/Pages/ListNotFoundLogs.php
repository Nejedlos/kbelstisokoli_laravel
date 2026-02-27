<?php

namespace App\Filament\Resources\NotFoundLogs\Pages;

use App\Filament\Resources\NotFoundLogs\NotFoundLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotFoundLogs extends ListRecords
{
    protected static string $resource = NotFoundLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

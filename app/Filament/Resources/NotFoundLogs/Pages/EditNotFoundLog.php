<?php

namespace App\Filament\Resources\NotFoundLogs\Pages;

use App\Filament\Resources\NotFoundLogs\NotFoundLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotFoundLog extends EditRecord
{
    protected static string $resource = NotFoundLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

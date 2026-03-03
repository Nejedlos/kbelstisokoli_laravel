<?php

namespace App\Filament\Resources\ExternalImportRuns\Pages;

use App\Filament\Resources\ExternalImportRuns\ExternalImportRunResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalImportRun extends EditRecord
{
    protected static string $resource = ExternalImportRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ExternalImportRuns\Pages;

use App\Filament\Resources\ExternalImportRuns\ExternalImportRunResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalImportRuns extends ListRecords
{
    protected static string $resource = ExternalImportRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

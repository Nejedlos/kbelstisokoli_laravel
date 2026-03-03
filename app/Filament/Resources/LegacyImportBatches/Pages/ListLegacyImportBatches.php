<?php

namespace App\Filament\Resources\LegacyImportBatches\Pages;

use App\Filament\Resources\LegacyImportBatches\LegacyImportBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLegacyImportBatches extends ListRecords
{
    protected static string $resource = LegacyImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

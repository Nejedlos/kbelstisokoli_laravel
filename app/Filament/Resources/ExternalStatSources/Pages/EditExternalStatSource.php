<?php

namespace App\Filament\Resources\ExternalStatSources\Pages;

use App\Filament\Resources\ExternalStatSources\ExternalStatSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalStatSource extends EditRecord
{
    protected static string $resource = ExternalStatSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

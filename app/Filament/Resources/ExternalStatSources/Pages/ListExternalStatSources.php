<?php

namespace App\Filament\Resources\ExternalStatSources\Pages;

use App\Filament\Resources\ExternalStatSources\ExternalStatSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalStatSources extends ListRecords
{
    protected static string $resource = ExternalStatSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

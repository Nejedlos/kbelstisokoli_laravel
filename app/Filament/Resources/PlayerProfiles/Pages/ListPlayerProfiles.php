<?php

namespace App\Filament\Resources\PlayerProfiles\Pages;

use App\Filament\Resources\PlayerProfiles\PlayerProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlayerProfiles extends ListRecords
{
    protected static string $resource = PlayerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\UserSeasonConfigs\Pages;

use App\Filament\Resources\UserSeasonConfigs\UserSeasonConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserSeasonConfigs extends ListRecords
{
    protected static string $resource = UserSeasonConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

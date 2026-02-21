<?php

namespace App\Filament\Resources\StatisticSets\Pages;

use App\Filament\Resources\StatisticSets\StatisticSetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStatisticSets extends ListRecords
{
    protected static string $resource = StatisticSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\StatisticSets\Pages;

use App\Filament\Resources\StatisticSets\StatisticSetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStatisticSet extends EditRecord
{
    protected static string $resource = StatisticSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

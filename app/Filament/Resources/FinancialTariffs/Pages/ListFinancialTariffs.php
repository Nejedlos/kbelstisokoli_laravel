<?php

namespace App\Filament\Resources\FinancialTariffs\Pages;

use App\Filament\Resources\FinancialTariffs\FinancialTariffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinancialTariffs extends ListRecords
{
    protected static string $resource = FinancialTariffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

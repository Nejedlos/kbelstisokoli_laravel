<?php

namespace App\Filament\Resources\FinancialTariffs\Pages;

use App\Filament\Resources\FinancialTariffs\FinancialTariffResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinancialTariff extends EditRecord
{
    protected static string $resource = FinancialTariffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

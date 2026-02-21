<?php

namespace App\Filament\Resources\FinanceCharges\Pages;

use App\Filament\Resources\FinanceCharges\FinanceChargeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinanceCharge extends EditRecord
{
    protected static string $resource = FinanceChargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

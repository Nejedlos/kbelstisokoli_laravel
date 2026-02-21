<?php

namespace App\Filament\Resources\FinanceCharges\Pages;

use App\Filament\Resources\FinanceCharges\FinanceChargeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinanceCharge extends CreateRecord
{
    protected static string $resource = FinanceChargeResource::class;

    protected function afterCreate(): void
    {
        event(new \App\Events\FinanceChargeCreated($this->record));
    }
}

<?php

namespace App\Filament\Resources\FinanceCharges\Pages;

use App\Filament\Resources\FinanceCharges\FinanceChargeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinanceCharges extends ListRecords
{
    protected static string $resource = FinanceChargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

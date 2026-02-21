<?php

namespace App\Filament\Resources\FinancePayments\Pages;

use App\Filament\Resources\FinancePayments\FinancePaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinancePayments extends ListRecords
{
    protected static string $resource = FinancePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

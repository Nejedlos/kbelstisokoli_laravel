<?php

namespace App\Filament\Resources\FinancePayments\Pages;

use App\Filament\Resources\FinancePayments\FinancePaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinancePayment extends EditRecord
{
    protected static string $resource = FinancePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

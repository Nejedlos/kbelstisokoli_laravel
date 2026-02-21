<?php

namespace App\Filament\Resources\FinancePayments\Pages;

use App\Filament\Resources\FinancePayments\FinancePaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancePayment extends CreateRecord
{
    protected static string $resource = FinancePaymentResource::class;
}

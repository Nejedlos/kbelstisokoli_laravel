<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case Card = 'card';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BankTransfer => __('Bankovní převod'),
            self::Cash => __('Hotovost'),
            self::Card => __('Karta'),
            self::Other => __('Jiné'),
        };
    }
}

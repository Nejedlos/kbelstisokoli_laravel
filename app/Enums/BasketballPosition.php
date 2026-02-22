<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BasketballPosition: string implements HasLabel
{
    case PG = 'PG';
    case SG = 'SG';
    case SF = 'SF';
    case PF = 'PF';
    case C = 'C';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PG => __('PG - Rozehrávač'),
            self::SG => __('SG - Střílející rozehrávač'),
            self::SF => __('SF - Malé křídlo'),
            self::PF => __('PF - Přesilové křídlo'),
            self::C => __('C - Pivot'),
        };
    }
}

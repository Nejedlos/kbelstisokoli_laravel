<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JerseySize: string implements HasLabel
{
    case YXS = 'YXS';
    case YS = 'YS';
    case YM = 'YM';
    case YL = 'YL';
    case YXL = 'YXL';
    case S = 'S';
    case M = 'M';
    case L = 'L';
    case XL = 'XL';
    case XXL = 'XXL';
    case XXXL = 'XXXL';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}

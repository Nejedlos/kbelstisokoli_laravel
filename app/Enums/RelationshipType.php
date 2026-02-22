<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RelationshipType: string implements HasLabel
{
    case Mother = 'mother';
    case Father = 'father';
    case Guardian = 'guardian';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Mother => __('Matka'),
            self::Father => __('Otec'),
            self::Guardian => __('Zákonný zástupce'),
            self::Other => __('Jiné'),
        };
    }
}

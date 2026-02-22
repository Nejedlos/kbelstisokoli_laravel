<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DominantHand: string implements HasLabel
{
    case Left = 'left';
    case Right = 'right';
    case Both = 'both';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Left => __('Levák'),
            self::Right => __('Pravák'),
            self::Both => __('Obě ruce'),
        };
    }
}

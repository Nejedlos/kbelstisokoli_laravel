<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasColor, HasLabel
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Male => __('Muž'),
            self::Female => __('Žena'),
            self::Other => __('Jiné'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Male => 'info',
            self::Female => 'danger',
            self::Other => 'gray',
        };
    }
}

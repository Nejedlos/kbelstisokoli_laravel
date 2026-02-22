<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MembershipStatus: string implements HasLabel, HasColor
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Inactive = 'inactive';
    case Former = 'former';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => __('Čekající'),
            self::Active => __('Aktivní'),
            self::Suspended => __('Pozastavené'),
            self::Inactive => __('Neaktivní'),
            self::Former => __('Bývalý člen'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Suspended => 'danger',
            self::Inactive => 'gray',
            self::Former => 'gray',
        };
    }
}

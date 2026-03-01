<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExcuseReason: string implements HasLabel, HasColor, HasIcon
{
    case Illness = 'illness';
    case Injury = 'injury';
    case Work = 'work';
    case Family = 'family';
    case Holiday = 'holiday';
    case Other = 'other';

    public function getLabel(): ?string
    {
        return __('member.attendance.excuse_reasons.' . $this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Illness, self::Injury => 'danger',
            self::Work, self::Family => 'warning',
            self::Holiday => 'info',
            self::Other => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Illness => 'fa-light fa-head-side-cough',
            self::Injury => 'fa-light fa-crutch',
            self::Work => 'fa-light fa-briefcase',
            self::Family => 'fa-light fa-house-user',
            self::Holiday => 'fa-light fa-plane',
            self::Other => 'fa-light fa-circle-question',
        };
    }
}

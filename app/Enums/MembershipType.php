<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MembershipType: string implements HasLabel
{
    case Player = 'player';
    case Coach = 'coach';
    case Parent = 'parent';
    case Staff = 'staff';
    case Fan = 'fan';
    case Honorary = 'honorary';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Player => __('Hráč'),
            self::Coach => __('Trenér'),
            self::Parent => __('Rodič'),
            self::Staff => __('Personál'),
            self::Fan => __('Fanoušek'),
            self::Honorary => __('Čestný člen'),
        };
    }
}

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

    /**
     * System role which is automatically managed by this membership type.
     *
     * Staff, fans and honorary members do not have a dedicated system role.
     */
    public function roleName(): ?string
    {
        return match ($this) {
            self::Player => 'player',
            self::Coach => 'coach',
            self::Parent => 'parent',
            self::Staff, self::Fan, self::Honorary => null,
        };
    }

    /**
     * Roles which may be added or removed by membership synchronization.
     * Privileged roles such as admin and editor are intentionally excluded.
     *
     * @return list<string>
     */
    public static function managedRoleNames(): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn (self $type) => $type->roleName(), self::cases())
        )));
    }

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

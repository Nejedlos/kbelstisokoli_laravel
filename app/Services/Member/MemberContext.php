<?php

namespace App\Services\Member;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class MemberContext
{
    protected const SESSION_KEY = 'member_active_team_id';

    /**
     * Vrátí ID aktivního týmu ze session nebo z profilu.
     */
    public function getActiveTeamId(): ?int
    {
        // Pokud uživatel není přihlášen, nemá smysl pokračovat
        if (! auth()->check()) {
            return null;
        }

        // 1. Zkusíme session (null v session znamená "Všechny týmy")
        if (Session::exists(self::SESSION_KEY)) {
            return Session::get(self::SESSION_KEY);
        }

        /** @var User $user */
        $user = auth()->user();

        // 2. Pokud je v profilu nastaveno "zobrazit vše", vrátíme null
        if ($user->member_view_all_by_default) {
            return null;
        }

        // 3. Zkusíme primární tým z aktivního hráčského profilu
        $primaryTeamId = $user->activePlayerProfile?->primary_team_id;
        if ($primaryTeamId) {
            return $primaryTeamId;
        }

        // 4. Zkusíme defaultní tým z profilu (v modelu User)
        if ($user->member_default_team_id) {
            return $user->member_default_team_id;
        }

        // 5. Zkusíme první tým uživatele (hráčský profil -> týmy)
        $firstTeamId = $user->teams()->first()?->id;

        return $firstTeamId;
    }

    /**
     * Nastaví ID aktivního týmu do session.
     */
    public function setActiveTeamId(?int $id): void
    {
        Session::put(self::SESSION_KEY, $id);
    }

    /**
     * Vrátí seznam všech aktivních týmů pro select.
     */
    public function getAvailableTeams(): Collection
    {
        return Team::query()
            ->orderBy('name')
            ->get();
    }
}

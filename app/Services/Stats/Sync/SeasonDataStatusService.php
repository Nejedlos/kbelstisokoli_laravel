<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\StatisticRow;

class SeasonDataStatusService
{
    /**
     * Zjistí, zda je sezóna pro daný tým považována za "prázdnou" z hlediska externích dat.
     */
    public function isEmpty(int $teamId, int $seasonId): bool
    {
        // A) Neexistuje konfigurace pro externí synchronizaci
        if (!ExternalTeamSeasonConfig::where('team_id', $teamId)->where('season_id', $seasonId)->exists()) {
            return true;
        }

        // B) Neexistují žádné zápasy
        if (!BasketballMatch::where('team_id', $teamId)->where('season_id', $seasonId)->exists()) {
            return true;
        }

        // C) Neexistují žádné statistiky (boxscore nebo sumáře)
        if (!StatisticRow::where('team_id', $teamId)->where('season_id', $seasonId)->exists()) {
            return true;
        }

        return false;
    }
}

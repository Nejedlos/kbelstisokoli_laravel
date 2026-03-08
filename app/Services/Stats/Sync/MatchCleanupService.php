<?php

namespace App\Services\Stats\Sync;

use App\Models\BasketballMatch;
use App\Support\ConsoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatchCleanupService
{
    /**
     * Vyhledá a sloučí duplicitní zápasy v celé databázi.
     * Pravidlo: Jeden tým nemůže hrát dva zápasy ve stejný čas (tolerance 120 min).
     *
     * @param bool $dryRun Pokud je true, pouze vypíše, co by udělal.
     * @return array Statistiky o provedené akci.
     */
    public function cleanupDuplicates(bool $dryRun = false): array
    {
        $stats = [
            'groups_found' => 0,
            'matches_merged' => 0,
            'attendances_moved' => 0,
        ];

        // 1. Najdeme týmy a dny, kde je více než jeden zápas
        $groups = BasketballMatch::select('team_id', 'season_id', 'is_home')
            ->selectRaw('DATE(scheduled_at) as match_date')
            ->groupBy('team_id', 'season_id', 'is_home', 'match_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $matches = BasketballMatch::where('team_id', $group->team_id)
                ->where('season_id', $group->season_id)
                ->where('is_home', $group->is_home)
                ->whereDate('scheduled_at', $group->match_date)
                ->orderBy('scheduled_at')
                ->get();

            // Seskupíme zápasy v rámci dne podle času (tolerance 120 min)
            $timeGroups = [];
            foreach ($matches as $match) {
                $foundGroup = false;
                foreach ($timeGroups as &$timeGroup) {
                    $firstMatch = $timeGroup[0];
                    if (abs($firstMatch->scheduled_at->diffInMinutes($match->scheduled_at)) <= 120) {
                        $timeGroup[] = $match;
                        $foundGroup = true;
                        break;
                    }
                }
                if (!$foundGroup) {
                    $timeGroups[] = [$match];
                }
            }

            foreach ($timeGroups as $timeGroup) {
                if (count($timeGroup) <= 1) {
                    continue;
                }

                $stats['groups_found']++;

                // Určíme hlavní zápas (ten z externího zdroje nebo ten s ID nejnižším)
                $mainMatch = collect($timeGroup)->first(fn($m) => !empty($m->metadata['source']));
                if (!$mainMatch) {
                    $mainMatch = $timeGroup[0];
                }

                $toMerge = collect($timeGroup)->filter(fn($m) => $m->id !== $mainMatch->id);

                foreach ($toMerge as $duplicate) {
                    if (!$dryRun) {
                        $this->mergeMatches($mainMatch, $duplicate);
                    }
                    $stats['matches_merged']++;
                    Log::info("Merged match {$duplicate->id} into {$mainMatch->id}");
                }
            }
        }

        return $stats;
    }

    /**
     * Sloučí duplicitní zápas do hlavního.
     */
    protected function mergeMatches(BasketballMatch $main, BasketballMatch $duplicate): void
    {
        DB::transaction(function () use ($main, $duplicate) {
            // 1. Přesun docházky
            $attendances = $duplicate->attendances;
            foreach ($attendances as $attendance) {
                // Pokud už hlavní zápas má docházku pro stejného uživatele, ignorujeme (nebo sloučíme)
                $exists = $main->attendances()->where('user_id', $attendance->user_id)->exists();
                if (!$exists) {
                    $attendance->update([
                        'attendable_id' => $main->id,
                    ]);
                } else {
                    $attendance->delete();
                }
            }

            // 2. Přesun statistik (pokud existují a hlavní je nemá)
            // Poznámka: V tomto projektu jsou statistiky vázány přes basketball_match_id v statistic_rows
            DB::table('statistic_rows')
                ->where('basketball_match_id', $duplicate->id)
                ->update(['basketball_match_id' => $main->id]);

            // 3. Přesun vazeb na týmy (m:n)
            $teams = DB::table('basketball_match_team')->where('basketball_match_id', $duplicate->id)->pluck('team_id');
            foreach ($teams as $teamId) {
                $exists = DB::table('basketball_match_team')
                    ->where('basketball_match_id', $main->id)
                    ->where('team_id', $teamId)
                    ->exists();
                if (!$exists) {
                    DB::table('basketball_match_team')->insert([
                        'basketball_match_id' => $main->id,
                        'team_id' => $teamId,
                    ]);
                }
            }
            DB::table('basketball_match_team')->where('basketball_match_id', $duplicate->id)->delete();

            // 4. Sloučení metadat a poznámek
            $mainMetadata = $main->metadata ?? [];
            $dupMetadata = $duplicate->metadata ?? [];
            $main->metadata = array_merge($dupMetadata, $mainMetadata); // Hlavní metadata vyhrávají

            if (empty($main->notes_internal) && !empty($duplicate->notes_internal)) {
                $main->notes_internal = $duplicate->notes_internal;
            }

            if (empty($main->location) && !empty($duplicate->location)) {
                $main->location = $duplicate->location;
            }

            $main->save();

            // 5. Smazání duplicity
            $duplicate->delete();
        });
    }
}

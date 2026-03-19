<?php

namespace App\Console\Commands;

use App\Models\BasketballMatch;
use App\Models\Team;
use App\Models\Season;
use App\Services\Stats\Sync\MatchSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StatsCleanupDuplicatesCommand extends Command
{
    protected $signature = 'stats:cleanup-duplicates {--dry-run : Pouze vypíše duplicity, ale nic nemaže}';
    protected $description = 'Najde a sloučí duplicitní zápasy (podle stejného external_id v rámci týmu a sezóny).';

    public function handle(MatchSyncService $matchSyncService)
    {
        $dryRun = $this->option('dry-run');

        $this->info('Vyhledávám duplicitní zápasy...');

        // Najdeme external_ids, která se vyskytují vícekrát pro stejný tým a sezónu
        // Používáme JSON extrakci pro MySQL
        $duplicates = DB::table('matches')
            ->select(
                'team_id',
                'season_id',
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.external_id")) as external_id'),
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull(DB::raw('JSON_EXTRACT(metadata, "$.external_id")'))
            ->groupBy('team_id', 'season_id', 'external_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('Nenalezeny žádné duplicity.');
            return self::SUCCESS;
        }

        $this->warn("Nalezeno {$duplicates->count()} skupin duplicitních zápasů.");

        foreach ($duplicates as $dup) {
            $this->line("Zpracovávám external_id: {$dup->external_id} (tým {$dup->team_id}, sezóna {$dup->season_id}, počet: {$dup->count})");

            if ($dryRun) {
                $matchIds = BasketballMatch::where('team_id', $dup->team_id)
                    ->where('season_id', $dup->season_id)
                    ->where('metadata->external_id', $dup->external_id)
                    ->pluck('id')
                    ->toArray();
                $this->info("  -> [DRY RUN] Sloučil bych zápasy: " . implode(', ', $matchIds));
                continue;
            }

            try {
                $team = \App\Models\Team::find($dup->team_id);
                $season = \App\Models\Season::find($dup->season_id);

                if (!$team || !$season) {
                    $this->error("  -> Chyba: Tým nebo sezóna nenalezeny.");
                    continue;
                }

                $mergedMatch = $matchSyncService->mergeDuplicatesByExternalId($team, $season, (string) $dup->external_id);
                $this->info("  -> Sloučeno do zápasu ID: {$mergedMatch->id}");
            } catch (\Exception $e) {
                $this->error("  -> Chyba při slučování: " . $e->getMessage());
            }
        }

        $this->info('Hotovo.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class QASmoke extends Command
{
    protected $signature = 'qa:smoke {--prod : Ověří produkční stav}';

    protected $description = 'Provede rychlý "smoke test" (web, auth, data presence)';

    public function handle()
    {
        $this->info('========================================');
        $this->info('  QA Smoke Test'.($this->option('prod') ? ' [PROD]' : ''));
        $this->info('========================================');

        $results = [
            'Web Availability' => $this->checkWeb(),
            'Data Presence' => $this->checkData(),
            'Import Integrity' => $this->checkImports(),
        ];

        if (in_array(false, array_values($results), true)) {
            $this->error("\n❌ Smoke test selhal!");

            return 1;
        }

        $this->info("\n✅ Smoke test úspěšný.");

        return 0;
    }

    private function checkWeb(): bool
    {
        $url = config('app.url');
        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $this->line("✅ Web [{$url}]: Dostupný (200).");

                return true;
            }
            $this->error("❌ Web [{$url}]: Vrátil kód {$response->status()}.");

            return false;
        } catch (\Exception $e) {
            $this->error("❌ Web [{$url}]: Chyba spojení - ".$e->getMessage());

            return false;
        }
    }

    private function checkData(): bool
    {
        $season = Season::where('is_active', true)->first();
        if (! $season) {
            $this->error('❌ Data: Žádná aktivní sezóna.');

            return false;
        }

        $matchCount = BasketballMatch::where('season_id', $season->id)->count();
        $this->line("ℹ️ Data: Aktivní sezóna {$season->name} má {$matchCount} zápasů.");

        if ($matchCount === 0) {
            $this->warn('⚠️ Data: V aktivní sezóně nejsou žádné zápasy.');
        }

        $teams = Team::whereIn('slug', ['muzi-c', 'muzi-e'])->get();
        foreach ($teams as $team) {
            $hasStats = StatisticRow::where('season_id', $season->id)
                ->where('team_id', $team->id)
                ->whereNotNull('basketball_match_id')
                ->exists();

            if ($hasStats) {
                $this->line("✅ Data [{$team->slug}]: Obsahuje statistiky zápasů.");
            } else {
                $this->warn("⚠️ Data [{$team->slug}]: Neobsahuje žádné statistiky zápasů.");
            }
        }

        return true;
    }

    private function checkImports(): bool
    {
        $lastRuns = ExternalImportRun::latest()->limit(5)->get();
        if ($lastRuns->isEmpty()) {
            $this->warn('⚠️ Import: Žádná historie importů.');

            return true;
        }

        $failed = $lastRuns->where('status', 'failed')->count();
        if ($failed > 0) {
            $this->error("❌ Import: V posledních 5 bězích je {$failed} chyb.");
            foreach ($lastRuns->where('status', 'failed') as $run) {
                $this->line("   - Run #{$run->id} [{$run->run_type}]: {$run->error_summary}");
            }

            return false;
        }

        $this->line('✅ Import: Poslední běhy jsou v pořádku (success/skipped/partial_failed).');

        return true;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Season;
use App\Models\Team;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class StatsHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ověří zdraví systému statistik a synchronizace (DB, fronty, storage, fetcher).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Provádím diagnostiku systému statistik...");
        $this->newLine();

        $health = $this->checkInfrastructure();
        $this->displayInfrastructure($health);

        $this->newLine();
        $this->info("Přehled synchronizace aktivní sezóny:");
        $this->displaySyncOverview();

        return self::SUCCESS;
    }

    protected function checkInfrastructure(): array
    {
        $status = [];

        // DB
        try {
            DB::connection()->getPdo();
            $status['db'] = ['label' => 'Database', 'ok' => true, 'msg' => 'Connected'];
        } catch (\Exception $e) {
            $status['db'] = ['label' => 'Database', 'ok' => false, 'msg' => $e->getMessage()];
        }

        // Queue
        try {
            $jobCount = DB::table('jobs')->count();
            $status['queue'] = [
                'label' => 'Queue (Jobs)',
                'ok' => true,
                'msg' => "{$jobCount} pending jobs",
                'warning' => $jobCount > 100
            ];
        } catch (\Exception $e) {
            $status['queue'] = ['label' => 'Queue (Jobs)', 'ok' => false, 'msg' => 'Table not found or DB error'];
        }

        // Scheduler
        $lastHeartbeat = Cache::get('scheduler_heartbeat');
        $isOk = $lastHeartbeat && $lastHeartbeat->diffInMinutes(now()) < 5;
        $status['scheduler'] = [
            'label' => 'Scheduler',
            'ok' => $isOk,
            'msg' => $lastHeartbeat ? 'Last run: ' . $lastHeartbeat->diffForHumans() : 'No heartbeat detected'
        ];

        // Storage
        try {
            Storage::disk('local')->put('health_test.txt', 'health check');
            Storage::disk('local')->delete('health_test.txt');
            $status['storage'] = ['label' => 'Storage', 'ok' => true, 'msg' => 'Writable'];
        } catch (\Exception $e) {
            $status['storage'] = ['label' => 'Storage', 'ok' => false, 'msg' => $e->getMessage()];
        }

        // External Fetcher (Ping cz.basketball)
        try {
            $response = Http::timeout(5)->get('https://cz.basketball');
            $status['fetcher'] = [
                'label' => 'External Fetcher',
                'ok' => $response->successful(),
                'msg' => $response->successful() ? 'cz.basketball reachable' : 'HTTP Status: ' . $response->status()
            ];
        } catch (\Exception $e) {
            $status['fetcher'] = ['label' => 'External Fetcher', 'ok' => false, 'msg' => 'Connection failed'];
        }

        return $status;
    }

    protected function displayInfrastructure(array $health): void
    {
        $rows = [];
        foreach ($health as $item) {
            $status = $item['ok'] ? '<info>OK</info>' : '<error>FAIL</error>';
            if (isset($item['warning']) && $item['warning']) {
                $status = '<comment>WARN</comment>';
            }
            $rows[] = [$item['label'], $status, $item['msg']];
        }

        $this->table(['Komponenta', 'Stav', 'Detaily'], $rows);
    }

    protected function displaySyncOverview(): void
    {
        $activeSeason = Season::where('is_active', true)->first();
        if (!$activeSeason) {
            $this->warn("Žádná aktivní sezóna nebyla nalezena.");
            return;
        }

        $this->line("Aktivní sezóna: <comment>{$activeSeason->name}</comment>");

        $teamSlugs = config('external_sources.czbasketball.teams', []);
        $rows = [];

        foreach ($teamSlugs as $slug) {
            $team = Team::where('slug', $slug)->first();
            if (!$team) continue;

            $lastSync = ExternalImportRun::where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->where('status', 'success')
                ->latest('finished_at')
                ->first();

            $matchCount = DB::table('matches')
                ->where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->whereNotNull('metadata->external->season_external_match_id')
                ->count();

            $boxscoreSet = StatisticSet::where('slug', 'match-boxscore')->first();
            $statRowsCount = $boxscoreSet ? StatisticRow::where('statistic_set_id', $boxscoreSet->id)
                ->where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->count() : 0;

            $unmatchedCount = DB::table('external_entity_mappings')
                ->where('source_key', 'czbasketball')
                ->where('season_id', $activeSeason->id)
                ->where('entity_type', 'player')
                ->whereNull('internal_id')
                ->count();

            $rows[] = [
                $team->name,
                $lastSync ? $lastSync->finished_at->diffForHumans() : 'Never',
                $matchCount,
                $statRowsCount,
                $unmatchedCount > 0 ? "<comment>{$unmatchedCount}</comment>" : $unmatchedCount
            ];
        }

        $this->table(['Tým', 'Poslední sync', 'Zápasy', 'Stat. řádky', 'Unmatched'], $rows);
    }
}

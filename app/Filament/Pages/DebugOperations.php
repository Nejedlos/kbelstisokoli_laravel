<?php

namespace App\Filament\Pages;

use App\Jobs\Stats\SyncMatchDetailJob;
use App\Jobs\Stats\SyncTeamSeasonJob;
use App\Models\BasketballMatch;
use App\Models\ExternalImportRun;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\LegacyImportBatch;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\Team;
use App\Services\Support\ConsoleService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DebugOperations extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string|\UnitEnum|null $navigationGroup = 'Systém';

    protected static ?string $navigationLabel = 'Debug / Operations';

    protected static ?string $title = 'Debug & Operations Panel';

    protected string $view = 'filament.pages.debug-operations';

    public string $consoleOutput = '';

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage_advanced_settings');
    }

    public function mount(): void
    {
        $this->consoleOutput = ConsoleService::getContent();
    }

    public function refreshConsoleLogs(): void
    {
        $newContent = ConsoleService::getContent();
        if ($this->consoleOutput !== $newContent) {
            $this->consoleOutput = $newContent;
            $this->dispatch('console-updated');
        }
    }

    public function clearConsoleLogs(): void
    {
        ConsoleService::clear();
        $this->consoleOutput = '';
        Notification::make()->title('Console cleared')->success()->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
    }

    protected function getViewData(): array
    {
        return [
            'health' => $this->getHealthStatus(),
            'externalSync' => $this->getExternalSyncStats(),
            'legacyImport' => $this->getLegacyImportStats(),
            'auditLogs' => $this->getAuditLogs(),
            'discoveryStats' => $this->getDiscoveryStats(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncAll')
                ->label('Sync All Active')
                ->icon('heroicon-m-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $activeSeason = Season::where('is_active', true)->first();
                    if (! $activeSeason) {
                        Notification::make()->title('No active season found')->danger()->send();

                        return;
                    }

                    ConsoleService::log("Spouštím synchronizaci všech aktivních týmů pro sezónu: {$activeSeason->name}");

                    $teams = config('external_sources.czbasketball.teams', []);
                    foreach ($teams as $teamSlug) {
                        $team = Team::where('slug', $teamSlug)->first();
                        if ($team) {
                            ConsoleService::log("- Naplánováno pro tým: {$team->name}", 'info');
                            SyncTeamSeasonJob::dispatch($team->id, $activeSeason->id);
                        }
                    }

                    Notification::make()->title('Sync jobs dispatched')->success()->send();
                }),

            Action::make('discoverSeasons')
                ->label('Discover Missing Seasons')
                ->icon('heroicon-m-magnifying-glass')
                ->color('info')
                ->requiresConfirmation()
                ->action(function () {
                    ConsoleService::log('Zahajuji proces vyhledávání chybějících sezón (Discovery)...', 'info');
                    $discoveryService = app(\App\Services\Stats\Sync\SeasonDiscoveryService::class);
                    $results = $discoveryService->discover();

                    $found = count(array_filter($results, fn ($r) => $r['status'] !== 'not found'));

                    ConsoleService::log("Discovery dokončeno. Nalezeno a vytvořeno: {$found} nových konfigurací.", 'success');

                    Notification::make()
                        ->title('Discovery finished')
                        ->body("Found and created {$found} new season configurations.")
                        ->success()
                        ->send();
                }),

            Action::make('recomputeAll')
                ->label('Recompute Stats')
                ->icon('heroicon-m-calculator')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $activeSeason = Season::where('is_active', true)->first();
                    $teams = config('external_sources.czbasketball.teams', []);

                    ConsoleService::log("Spouštím přepočet statistik pro sezónu: ".($activeSeason?->name ?? 'N/A'));

                    foreach ($teams as $teamSlug) {
                        $team = Team::where('slug', $teamSlug)->first();
                        if ($team && $activeSeason) {
                            ConsoleService::log("- Přepočítávám tým: {$team->name}", 'info');
                            $statService = app(\App\Services\Stats\Sync\StatisticSyncService::class);
                            $statService->recomputePlayerSummaries($activeSeason->id);
                            $statService->recomputeTeamSummary($activeSeason->id, $team->id);
                        }
                    }

                    ConsoleService::log('Přepočet statistik dokončen.', 'success');
                    Notification::make()->title('Aggregations recomputed for active season')->success()->send();
                }),
        ];
    }

    protected function getHealthStatus(): array
    {
        $status = [];

        // DB
        try {
            $pdo = DB::connection()->getPdo();
            $version = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $status['db'] = ['label' => 'Database', 'ok' => true, 'msg' => "Connected ({$version})"];
        } catch (\Exception $e) {
            $status['db'] = ['label' => 'Database', 'ok' => false, 'msg' => $e->getMessage()];
        }

        // Queue
        $jobCount = DB::table('jobs')->count();
        $status['queue'] = [
            'label' => 'Queue (Jobs)',
            'ok' => true,
            'msg' => "{$jobCount} pending jobs",
            'warning' => $jobCount > 100,
        ];

        // Scheduler
        $lastHeartbeat = Cache::get('scheduler_heartbeat');
        $isOk = $lastHeartbeat && $lastHeartbeat->diffInMinutes(now()) < 5;
        $status['scheduler'] = [
            'label' => 'Scheduler',
            'ok' => $isOk,
            'msg' => $lastHeartbeat ? 'Last run: '.$lastHeartbeat->diffForHumans() : 'No heartbeat detected',
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
                'msg' => $response->successful() ? 'cz.basketball reachable' : 'HTTP Status: '.$response->status(),
            ];
        } catch (\Exception $e) {
            $status['fetcher'] = ['label' => 'External Fetcher', 'ok' => false, 'msg' => 'Connection failed'];
        }

        return $status;
    }

    protected function getExternalSyncStats(): array
    {
        $activeSeason = Season::where('is_active', true)->first();
        if (! $activeSeason) {
            return [];
        }

        $teamSlugs = config('external_sources.czbasketball.teams', []);
        $stats = [];

        foreach ($teamSlugs as $slug) {
            $team = Team::where('slug', $slug)->first();
            if (! $team) {
                continue;
            }

            $config = ExternalTeamSeasonConfig::where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->first();

            $lastSync = ExternalImportRun::where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->where('status', 'success')
                ->latest('finished_at')
                ->first();

            $matchCount = BasketballMatch::query()
                ->where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->where('metadata', 'LIKE', '%"season_external_match_id":%')
                ->count();

            $boxscoreSet = StatisticSet::where('slug', 'match-boxscore')->first();
            $statRowsCount = $boxscoreSet ? StatisticRow::where('statistic_set_id', $boxscoreSet->id)
                ->where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->count() : 0;

            // Unmatched players
            $unmatchedCount = DB::table('external_entity_mappings')
                ->where('source_key', 'czbasketball')
                ->where('season_id', $activeSeason->id)
                ->where('entity_type', 'player')
                ->whereNull('internal_id')
                ->count();

            $lastError = ExternalImportRun::where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->whereIn('status', ['failed', 'partial_failed'])
                ->latest()
                ->first();

            $stats[] = [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'enabled' => $config?->is_enabled ?? false,
                'last_sync' => $lastSync?->finished_at,
                'match_count' => $matchCount,
                'stat_rows_count' => $statRowsCount,
                'unmatched_count' => $unmatchedCount,
                'last_error' => $lastError?->error_summary,
                'last_error_id' => $lastError?->id,
            ];
        }

        return $stats;
    }

    protected function getLegacyImportStats(): ?array
    {
        $lastBatch = LegacyImportBatch::latest()->first();
        if (! $lastBatch) {
            return null;
        }

        return [
            'id' => $lastBatch->id,
            'title' => $lastBatch->title ?? "Batch #{$lastBatch->id}",
            'status' => $lastBatch->status,
            'progress' => $lastBatch->total_files > 0 ? round(($lastBatch->processed_files / $lastBatch->total_files) * 100) : 0,
            'success' => $lastBatch->success_files,
            'failed' => $lastBatch->failed_files,
            'total' => $lastBatch->total_files,
        ];
    }

    protected function getAuditLogs(): \Illuminate\Support\Collection
    {
        return ExternalImportRun::with(['team', 'season'])
            ->latest()
            ->limit(20)
            ->get();
    }

    protected function getDiscoveryStats(): array
    {
        $emptyCount = 0;
        $teams = Team::whereHas('externalMappings')->get();
        $seasons = Season::all();
        $statusService = app(\App\Services\Stats\Sync\SeasonDataStatusService::class);

        foreach ($teams as $team) {
            foreach ($seasons as $season) {
                if ($statusService->isEmpty($team->id, $season->id)) {
                    $emptyCount++;
                }
            }
        }

        return [
            'empty_count' => $emptyCount,
            'last_discover' => ExternalImportRun::where('run_type', 'season_discover')->latest()->first()?->created_at,
        ];
    }

    // --- Action Handlers ---

    public function runTeamSync(int $teamId): void
    {
        $activeSeason = Season::where('is_active', true)->first();
        if (! $activeSeason) {
            return;
        }

        $team = Team::find($teamId);
        ConsoleService::log("Ruční spuštění synchronizace pro tým: ".($team?->name ?? $teamId), 'info');

        SyncTeamSeasonJob::dispatch($teamId, $activeSeason->id);
        Notification::make()->title('Sync started for team')->success()->send();
    }

    public function forceMatchSync(string $externalMatchId): void
    {
        $activeSeason = Season::where('is_active', true)->first();
        $team = Team::whereIn('slug', config('external_sources.czbasketball.teams'))->first(); // Fallback to first configured team

        if (! $activeSeason || ! $team) {
            return;
        }

        SyncMatchDetailJob::dispatch($team->id, $activeSeason->id, $externalMatchId, ['force' => true]);
        Notification::make()->title('Force match sync dispatched')->success()->send();
    }
}

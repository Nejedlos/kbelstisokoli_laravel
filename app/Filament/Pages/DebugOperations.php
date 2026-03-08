<?php

namespace App\Filament\Pages;

use App\Jobs\Stats\DiscoverSeasonsJob;
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
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
            'activeSeason' => Season::where('is_active', true)->first(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('stopSync')
                ->label('ZASTAVIT SYNCHRONIZACI')
                ->tooltip('Okamžitě zastaví všechny běžící a naplánované synchronizační joby (využívá stop-flag v cache).')
                ->icon(IconHelper::render(IconHelper::CANCEL))
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Zastavit synchronizaci?')
                ->modalDescription('Tato akce nastaví příznak pro zastavení všech běžících synchronizací. Joby, které již začaly, se ukončí při dalším kontrolním bodu.')
                ->action(function () {
                    ConsoleService::requestStop();
                    Notification::make()->title('Požadavek na zastavení byl odeslán.')->warning()->send();
                }),

            ActionGroup::make([
                Action::make('syncAll')
                    ->label('Hromadná synchronizace (sezóna)')
                    ->tooltip('Spustí synchronizaci (soupiska, zápasy, statistiky) pro VŠECHNY týmy ve vybrané sezóně.')
                    ->modalHeading('Hromadná synchronizace')
                    ->modalDescription('Tato akce zařadí do fronty synchronizaci pro všechny týmy ve vybrané sezóně.')
                    ->icon(IconHelper::render(IconHelper::REFRESH))
                    ->color('primary')
                    ->form([
                        \Filament\Forms\Components\Select::make('season_id')
                            ->label('Sezóna')
                            ->helperText('Vyberte sezónu, pro kterou chcete spustit import dat (soupisky, zápasy, statistiky).')
                            ->options(Season::query()->orderBy('name', 'desc')->pluck('name', 'id'))
                            ->default(fn () => Season::where('is_active', true)->first()?->id)
                            ->required(),
                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                \Filament\Forms\Components\Toggle::make('force')
                                    ->label('Force mode')
                                    ->helperText('Ignoruje hash obsahu.')
                                    ->onColor('warning'),
                                \Filament\Forms\Components\Toggle::make('fresh')
                                    ->label('Fresh mode')
                                    ->helperText('Smaže a znovu importuje (nebezpečné!).')
                                    ->onColor('danger'),
                                \Filament\Forms\Components\Toggle::make('ai')
                                    ->label('AI mode')
                                    ->helperText('Použije OpenAI pro synchronizaci.')
                                    ->onColor('info'),
                            ]),
                        \Filament\Schemas\Components\Section::make(new HtmlString(IconHelper::render(IconHelper::LIST_ICON) . ' Rozsah synchronizace'))
                            ->schema([
                                \Filament\Forms\Components\Toggle::make('sync_roster')
                                    ->label('Soupiska')
                                    ->default(true),
                                \Filament\Forms\Components\Toggle::make('sync_matches')
                                    ->label('Zápasy')
                                    ->default(true),
                                \Filament\Forms\Components\Toggle::make('sync_details')
                                    ->label('Statistiky')
                                    ->default(true)
                                    ->hidden(fn ($get) => ! $get('sync_matches')),
                                \Filament\Forms\Components\TextInput::make('max_match_details')
                                    ->label('Max detailů')
                                    ->numeric()
                                    ->default(100)
                                    ->helperText('Limit pro počet zápasů k hloubkové synchronizaci.')
                                    ->hidden(fn ($get) => ! $get('sync_details')),
                            ])->columns(2),
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data) {
                        $seasonId = $data['season_id'];
                        $season = Season::find($seasonId);

                        if (! $season) {
                            Notification::make()->title('Season not found')->danger()->send();

                            return;
                        }

                        $mode = '';
                        if ($data['ai'] ?? false) {
                            $mode = ' (AI FRESH)';
                        } elseif ($data['fresh'] ?? false) {
                            $mode = ' (FRESH)';
                        } elseif ($data['force'] ?? false) {
                            $mode = ' (FORCE)';
                        }

                        ConsoleService::log("Spouštím synchronizaci všech týmů{$mode} pro sezónu: {$season->name}");
                        ConsoleService::resetStop();

                        $teams = Team::whereHas('externalMappings')->get();
                        foreach ($teams as $team) {
                            ConsoleService::log("- Naplánováno pro tým: {$team->name}", 'info');
                            SyncTeamSeasonJob::dispatch($team->id, $season->id, [
                                'force' => $data['force'] ?? false,
                                'fresh' => $data['fresh'] ?? false,
                                'ai' => $data['ai'] ?? false,
                                'sync_roster' => $data['sync_roster'] ?? true,
                                'sync_matches' => $data['sync_matches'] ?? true,
                                'sync_details' => $data['sync_details'] ?? true,
                                'maxMatchDetails' => $data['max_match_details'] ?? null,
                            ]);
                        }

                        Notification::make()->title('Sync jobs dispatched'.($mode ?: ''))->success()->send();
                    }),

                Action::make('syncAllSeasons')
                    ->label('Hromadný import sezón')
                    ->tooltip('Spustí synchronizaci pro všechny týmy a vybrané sezóny (nebo všechny), které mají konfiguraci.')
                    ->icon(IconHelper::render(IconHelper::REFRESH))
                    ->color('primary')
                    ->form([
                        \Filament\Forms\Components\Select::make('season_id')
                            ->label('Sezóna')
                            ->helperText('Ponechte prázdné pro VŠECHNY sezóny.')
                            ->options(Season::query()->orderBy('name', 'desc')->pluck('name', 'id'))
                            ->nullable(),
                        \Filament\Forms\Components\Toggle::make('force')
                            ->label('Force mode')
                            ->helperText('Ignoruje hash obsahu u synchronizace.')
                            ->default(false),
                        \Filament\Forms\Components\TextInput::make('max_match_details')
                            ->label('Max detailů zápasů')
                            ->numeric()
                            ->default(100)
                            ->helperText('Limit pro hloubkovou synchronizaci zápasů (na jeden tým).'),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Hromadný import sezón')
                    ->modalDescription('Tato akce zařadí do fronty synchronizaci pro všechny týmy a vybrané (nebo všechny) sezóny. Může trvat dlouho.')
                    ->action(function (array $data) {
                        $seasonId = $data['season_id'] ?? null;
                        $seasons = $seasonId ? Season::where('id', $seasonId)->get() : Season::all();
                        $teams = Team::whereHas('externalMappings')->get();

                        if ($seasonId) {
                            $seasonName = Season::find($seasonId)?->name ?? '???';
                            ConsoleService::log("Spouštím synchronizaci pro sezónu: {$seasonName} (celkem: " . $teams->count() . " týmů).");
                        } else {
                            ConsoleService::log('Spouštím hromadnou synchronizaci VŠECH sezón (celkem: ' . $seasons->count() . ' sezón, ' . $teams->count() . ' týmů).');
                        }

                        foreach ($seasons as $season) {
                            if (! $seasonId) {
                                ConsoleService::log("Plánuji sezónu: {$season->name}", 'info');
                            }
                            foreach ($teams as $team) {
                                ConsoleService::log("  - Naplánováno: {$team->name}", 'info');
                                SyncTeamSeasonJob::dispatch($team->id, $season->id, [
                                    'force' => $data['force'] ?? false,
                                    'sync_roster' => true,
                                    'sync_matches' => true,
                                    'sync_details' => true,
                                    'maxMatchDetails' => $data['max_match_details'] ?? null,
                                ]);
                            }
                        }

                        Notification::make()->title($seasonId ? "Sync jobs dispatched for $seasonName" : 'All seasons sync jobs dispatched')->success()->send();
                    }),

                Action::make('fullSeasonProcess')
                    ->label('Kompletní zpracování sezóny (Sync + Přepočet)')
                    ->tooltip('Spustí synchronizaci dat a NÁSLEDNĚ (po naplánování jobů) provede přepočet statistik pro vybranou sezónu.')
                    ->icon(IconHelper::render(IconHelper::CIRCLE_CHECK))
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('season_id')
                            ->label('Sezóna')
                            ->helperText('Vyberte sezónu pro synchronizaci i přepočet.')
                            ->options(Season::query()->orderBy('name', 'desc')->pluck('name', 'id'))
                            ->default(fn () => Season::where('is_active', true)->first()?->id)
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('force')
                            ->label('Force mode')
                            ->helperText('Ignoruje hash obsahu u synchronizace.')
                            ->default(false),
                        \Filament\Forms\Components\TextInput::make('max_match_details')
                            ->label('Max detailů zápasů')
                            ->numeric()
                            ->default(100)
                            ->helperText('Limit pro hloubkovou synchronizaci zápasů (při plném zpracování doporučeno 100+).'),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Kompletní zpracování sezóny')
                    ->modalDescription('Tato akce spustí synchronizaci pro všechny týmy ve vybrané sezóně a následně spustí přepočet statistik nad aktuálními daty.')
                    ->action(function (array $data) {
                        $seasonId = $data['season_id'];
                        $season = Season::find($seasonId);
                        $teams = Team::whereHas('externalMappings')->get();

                        if (! $season) {
                            Notification::make()->title('Season not found')->danger()->send();
                            return;
                        }

                        ConsoleService::log("=== Zahajuji kompletní zpracování sezóny: {$season->name} ===");

                        // 1. Synchronizace (plánování jobů)
                        ConsoleService::log("KROK 1: Plánování synchronizace pro {$teams->count()} týmů...", 'info');
                        foreach ($teams as $team) {
                            ConsoleService::log("  - Plánuji: {$team->name}", 'debug');
                            SyncTeamSeasonJob::dispatch($team->id, $season->id, [
                                'force' => $data['force'] ?? false,
                                'sync_roster' => true,
                                'sync_matches' => true,
                                'sync_details' => true,
                                'maxMatchDetails' => $data['max_match_details'] ?? null,
                            ]);
                        }
                        ConsoleService::log("Synchronizační joby byly přidány do fronty. Poznámka: samotná data se v DB objeví až po doběhnutí jobů na pozadí.", 'warning');

                        // 2. Přepočet statistik (proběhne hned)
                        ConsoleService::log("KROK 2: Spouštím přepočet statistik nad aktuálními daty v DB...", 'info');
                        $statService = app(\App\Services\Stats\Sync\StatisticSyncService::class);
                        $statService->recomputePlayerSummaries($season->id);
                        foreach ($teams as $team) {
                            $statService->recomputeTeamSummary($season->id, $team->id);
                        }

                        ConsoleService::log("=== Kompletní zpracování sezóny {$season->name} dokončeno / iniciováno ===", 'success');
                        Notification::make()->title('Full season process initiated')->success()->send();
                    }),

                Action::make('discoverSeasons')
                    ->label('Hledat nové sezóny')
                    ->tooltip('Prohledá cz.basketball a najde ID (např. y=2024) pro chybějící sezóny. Pouze vytvoří konfiguraci (cíl importu), ale samotná data (soupisky, zápasy) stáhnete až následným hromadným importem.')
                    ->icon(IconHelper::render(IconHelper::SEO))
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Select::make('season_id')
                            ->label('Konkrétní sezóna')
                            ->helperText('Vyberte, pokud chcete hledat jen pro jednu sezónu. Jinak se projdou všechny prázdné.')
                            ->options(Season::query()->orderBy('name', 'desc')->pluck('name', 'id'))
                            ->nullable(),
                        \Filament\Forms\Components\Select::make('mode')
                            ->label('Spouštěcí mód')
                            ->options([
                                'sync' => 'Synchronně (v prohlížeči - hrozí timeout)',
                                'job' => 'Na pozadí (Job - doporučeno)',
                            ])
                            ->default('job')
                            ->required(),
                        \Filament\Forms\Components\Toggle::make('force')
                            ->label('Force mode')
                            ->helperText('Zkusí re-discovery i u sezón, které už konfiguraci mají.'),
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data) {
                        $seasonId = $data['season_id'] ?? null;
                        $seasonName = $seasonId ? Season::find($seasonId)?->name : null;

                        $options = [
                            'force' => $data['force'] ?? false,
                        ];

                        if ($data['mode'] === 'job') {
                            DiscoverSeasonsJob::dispatch(null, $seasonName, $options);
                            ConsoleService::log('Discovery proces ' . ($seasonName ? "pro sezónu $seasonName " : '') . 'naplánován jako job na pozadí.', 'info');
                            Notification::make()->title('Discovery job dispatched')->success()->send();

                            return;
                        }

                        // Synchronní běh (původní)
                        ConsoleService::log('Zahajuji proces vyhledávání chybějících sezón (Discovery)' . ($seasonName ? " pro sezónu $seasonName" : '') . '...', 'info');
                        $discoveryService = app(\App\Services\Stats\Sync\SeasonDiscoveryService::class);
                        $results = $discoveryService->discover(null, $seasonName, $options);

                        $found = count(array_filter($results, fn ($r) => ! in_array($r['status'], ['not found', 'error'])));

                        ConsoleService::log("Discovery dokončeno. Nalezeno a vytvořeno: {$found} nových konfigurací.", 'success');

                        Notification::make()
                            ->title('Discovery finished')
                            ->body("Found and created {$found} new season configurations.")
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Synchronizace')
                ->icon(IconHelper::render(IconHelper::REFRESH))
                ->color('primary')
                ->button(),

            ActionGroup::make([
                Action::make('recomputeAll')
                    ->label('Přepočítat aktuální sezónu')
                    ->tooltip('Přepočítá souhrnné statistiky (průměry, celky) pro vybranou sezónu na základě již stažených dat.')
                    ->icon(IconHelper::render(IconHelper::GAUGE))
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('season_id')
                            ->label('Sezóna')
                            ->helperText('Vyberte sezónu, pro kterou chcete přepočítat agregované statistiky hráčů a týmu.')
                            ->options(Season::query()->orderBy('name', 'desc')->pluck('name', 'id'))
                            ->default(fn () => Season::where('is_active', true)->first()?->id)
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data) {
                        $seasonId = $data['season_id'];
                        $season = Season::find($seasonId);
                        $teams = Team::whereHas('externalMappings')->get();

                        if (! $season) {
                            Notification::make()->title('Season not found')->danger()->send();
                            return;
                        }

                        ConsoleService::log("Spouštím přepočet statistik pro sezónu: {$season->name}");

                        foreach ($teams as $team) {
                            ConsoleService::log("- Přepočítávám tým: {$team->name}", 'info');
                            $statService = app(\App\Services\Stats\Sync\StatisticSyncService::class);
                            $statService->recomputePlayerSummaries($season->id);
                            $statService->recomputeTeamSummary($season->id, $team->id);
                        }

                        ConsoleService::log('Přepočet statistik dokončen.', 'success');
                        Notification::make()->title('Aggregations recomputed for season: ' . $season->name)->success()->send();
                    }),

                Action::make('recomputeAllSeasons')
                    ->label('Přepočítat sezóny')
                    ->tooltip('Přepočítá statistiky pro všechny týmy a vybrané sezóny (nebo všechny).')
                    ->icon(IconHelper::render(IconHelper::GAUGE))
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('season_id')
                            ->label('Sezóna')
                            ->helperText('Ponechte prázdné pro VŠECHNY sezóny.')
                            ->options(Season::query()->orderBy('name', 'desc')->pluck('name', 'id'))
                            ->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (array $data) {
                        $seasonId = $data['season_id'] ?? null;
                        $seasons = $seasonId ? Season::where('id', $seasonId)->get() : Season::all();
                        $teams = Team::whereHas('externalMappings')->get();
                        $statService = app(\App\Services\Stats\Sync\StatisticSyncService::class);

                        if ($seasonId) {
                            $seasonName = Season::find($seasonId)?->name ?? '???';
                            ConsoleService::log("Spouštím přepočet statistiky pro sezónu: {$seasonName} (celkem: " . $teams->count() . " týmů).");
                        } else {
                            ConsoleService::log('Spouštím hromadný přepočet VŠECH sezón (celkem: ' . $seasons->count() . ' sezón).');
                        }

                        foreach ($seasons as $season) {
                            ConsoleService::log("Zpracovávám sezónu: {$season->name}", 'info');
                            $statService->recomputePlayerSummaries($season->id);
                            foreach ($teams as $team) {
                                if (! $seasonId) {
                                    ConsoleService::log("  - Tým: {$team->name}", 'info');
                                }
                                $statService->recomputeTeamSummary($season->id, $team->id);
                            }
                        }

                        ConsoleService::log($seasonId ? "Přepočet sezóny $seasonName dokončen." : 'Hromadný přepočet dokončen.', 'success');
                        Notification::make()->title($seasonId ? "Aggregations recomputed for season: $seasonName" : 'Aggregations recomputed for ALL seasons')->success()->send();
                    }),
            ])
                ->label('Přepočty statistik')
                ->icon(IconHelper::render(IconHelper::GAUGE))
                ->color('warning')
                ->button(),
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
        if (! $lastHeartbeat) {
            $lastHeartbeat = Cache::store('file')->get('scheduler_heartbeat');
        }

        if (is_string($lastHeartbeat)) {
            try {
                $lastHeartbeat = \Illuminate\Support\Carbon::parse($lastHeartbeat);
            } catch (\Exception $e) {
                $lastHeartbeat = null;
            }
        }

        $isOk = $lastHeartbeat && $lastHeartbeat instanceof \Illuminate\Support\Carbon && $lastHeartbeat->diffInMinutes(now()) < 5;
        $status['scheduler'] = [
            'label' => 'Scheduler',
            'ok' => $isOk,
            'msg' => ($lastHeartbeat instanceof \Illuminate\Support\Carbon) ? 'Last run: '.$lastHeartbeat->diffForHumans() : 'No heartbeat detected',
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

        $boxscoreSet = StatisticSet::where('slug', 'match-boxscore-external')->first();

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
                ->get()
                ->filter(fn($m) => isset($m->metadata['external_id']) || isset($m->metadata['season_external_match_id']))
                ->count();

            $statRowsCount = $boxscoreSet ? StatisticRow::where('statistic_set_id', $boxscoreSet->id)
                ->where('team_id', $team->id)
                ->where('season_id', $activeSeason->id)
                ->count() : 0;

            // Unmatched players (Ghost users) pro tento tým
            $unmatchedCount = DB::table('external_entity_mappings')
                ->leftJoin('users', 'external_entity_mappings.internal_id', '=', 'users.id')
                ->leftJoin('player_profiles', 'users.id', '=', 'player_profiles.user_id')
                ->leftJoin('player_profile_team', 'player_profiles.id', '=', 'player_profile_team.player_profile_id')
                ->where('external_entity_mappings.source_key', 'czbasketball')
                ->where('external_entity_mappings.season_id', $activeSeason->id)
                ->where('external_entity_mappings.entity_type', 'player')
                ->where(function($q) use ($team) {
                    $q->where('player_profile_team.team_id', $team->id)
                      ->orWhereNull('external_entity_mappings.internal_id');
                })
                ->where(function ($q) {
                    $q->whereNull('external_entity_mappings.internal_id')
                        ->orWhere('users.metadata', 'LIKE', '%"is_ghost":true%');
                })
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
                'last_error_metadata' => $lastError?->metadata,
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
            ->withCount('logs')
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
        $this->executeTeamSync($teamId);
    }

    public function runTeamSyncForce(int $teamId): void
    {
        $this->executeTeamSync($teamId, ['force' => true]);
    }

    public function runTeamSyncFresh(int $teamId): void
    {
        $this->executeTeamSync($teamId, ['force' => true, 'fresh' => true]);
    }

    public function runTeamSyncAiFresh(int $teamId): void
    {
        $this->executeTeamSync($teamId, ['force' => true, 'fresh' => true, 'ai' => true]);
    }

    public function clearTeamErrors(int $teamId): void
    {
        $deletedCount = ExternalImportRun::where('team_id', $teamId)
            ->whereIn('status', ['failed', 'partial_failed'])
            ->delete();

        Notification::make()
            ->title("Historie chyb pro tým smazána")
            ->body("Bylo odstraněno {$deletedCount} neúspěšných záznamů z databáze.")
            ->success()
            ->send();
    }

    protected function executeTeamSync(int $teamId, array $options = []): void
    {
        $activeSeason = Season::where('is_active', true)->first();
        if (! $activeSeason) {
            Notification::make()->title('No active season found')->danger()->send();

            return;
        }

        $team = Team::find($teamId);
        $mode = '';
        if ($options['ai'] ?? false) {
            $mode = ' (AI FRESH)';
        } elseif ($options['fresh'] ?? false) {
            $mode = ' (FRESH)';
        } elseif ($options['force'] ?? false) {
            $mode = ' (FORCE)';
        }

        ConsoleService::log("Ruční spuštění synchronizace{$mode} pro tým: ".($team?->name ?? $teamId), 'info');
        ConsoleService::resetStop();

        SyncTeamSeasonJob::dispatch($teamId, $activeSeason->id, $options);
        Notification::make()->title('Sync started for team'.($mode ?: ''))->success()->send();
    }

    public function forceMatchSync(string $externalMatchId): void
    {
        $match = BasketballMatch::where('metadata', 'LIKE', '%"external_id":"'.$externalMatchId.'"%')->first();

        if (! $match) {
            Notification::make()->title('Zápas s externím ID '.$externalMatchId.' nebyl nalezen.')->danger()->send();

            return;
        }

        SyncMatchDetailJob::dispatch($match->id, ['force' => true]);
        Notification::make()->title('Force match sync dispatched pro zápas #'.$match->id)->success()->send();
    }

    public function downloadDebugHtml(int $runId): mixed
    {
        $run = \App\Models\ExternalImportRun::find($runId);
        if ($run && isset($run->metadata['debug_html_file'])) {
            $path = $run->metadata['debug_html_file'];
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
                return \Illuminate\Support\Facades\Storage::disk('local')->download($path, "run_{$runId}_debug.html");
            }
        }

        Notification::make()->title('Soubor nenalezen')->danger()->send();
        return null;
    }
}

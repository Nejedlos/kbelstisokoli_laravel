<?php

namespace App\Filament\Pages;

use App\Models\ExternalImportRun;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Support\BinaryHelper;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class SystemConsole extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-command-line';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected string $view = 'filament.pages.system-console';

    public string $consoleOutput = '';
    public string $output = '';
    public string $pollingInterval = '5s';

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage_advanced_settings');
    }

    public function mount(): void {}

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.system_console');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('admin.navigation.pages.system_console');
    }

    protected function getViewData(): array
    {
        return [
            'commandGroups' => $this->getCommandGroups(),
            'kpiData' => $this->getKpiData(),
        ];
    }

    protected function getKpiData(): array
    {
        // Cachujeme na 60 sekund, aby polling nebo časté refreshe nebrzdily systém
        return \Illuminate\Support\Facades\Cache::remember('system_console_kpi_data', 60, function () {
            $kpi = [
                'processes' => [],
                'imports' => [],
                'tables' => [],
            ];

            // 1. Artisan procesy (přes ps aux)
            if (function_exists('shell_exec')) {
                $psOutput = shell_exec('ps aux | grep artisan | grep -v grep');
                if ($psOutput) {
                    $lines = explode("\n", trim($psOutput));
                    foreach ($lines as $line) {
                        $parts = preg_split('/\s+/', trim($line));
                        if (count($parts) >= 11) {
                            $pid = $parts[1];
                            $cmd = implode(' ', array_slice($parts, 10));
                            $kpi['processes'][] = [
                                'pid' => $pid,
                                'cmd' => $cmd,
                                'is_stuck' => false,
                            ];
                        }
                    }
                }
            }

            // 2. Zaseknuté importy (ExternalImportRun)
            $kpi['imports'] = ExternalImportRun::where('status', 'running')
                ->where('updated_at', '<', now()->subMinutes(15))
                ->get()
                ->map(fn ($run) => [
                    'id' => $run->id,
                    'source' => $run->source_key,
                    'type' => $run->run_type,
                    'updated_at' => $run->updated_at,
                ])
                ->toArray();

            // 3. Tabulky k vyčištění
            $tablesToCheck = [
                'external_import_logs' => 5000,
                'activity_log' => 10000,
                'sessions' => 1000,
                'telescope_entries' => 5000,
            ];

            foreach ($tablesToCheck as $table => $threshold) {
                try {
                    if (Schema::hasTable($table)) {
                        // Používáme limit pro count na velkých tabulkách, pokud je to možné,
                        // nebo prostě jen count a spoléháme na cache.
                        $count = DB::table($table)->count();
                        if ($count > $threshold) {
                            $kpi['tables'][] = [
                                'name' => $table,
                                'count' => $count,
                            ];
                        }
                    }
                } catch (\Exception $e) {
                }
            }

            return $kpi;
        });
    }

    public function killProcess(int $pid): void
    {
        if (function_exists('shell_exec')) {
            shell_exec("kill -9 $pid");
            Cache::forget('system_console_kpi_data');
            Notification::make()
                ->title(__('admin/system-console.diagnostics.kpi.actions.process_killed', ['pid' => $pid]))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('admin/system-console.diagnostics.kpi.actions.process_kill_failed', ['pid' => $pid]))
                ->danger()
                ->send();
        }
    }

    public function fixStuckImport(int $id): void
    {
        $run = ExternalImportRun::find($id);
        if ($run) {
            $run->update([
                'status' => 'failed',
                'error_summary' => 'Terminated manually from System Console (Stuck detection).',
                'finished_at' => now(),
            ]);
            Cache::forget('system_console_kpi_data');
            Notification::make()
                ->title(__('admin/system-console.diagnostics.kpi.actions.import_fixed', ['id' => $id]))
                ->success()
                ->send();
        }
    }

    public function pruneTable(string $table): void
    {
        try {
            if ($table === 'telescope_entries') {
                // U Telescope preferujeme náš vlastní clear --all, který smaže i dnešní data,
                // protože uživatel kliká na čištění u konkrétní tabulky v KPI sekci.
                $allCommands = Artisan::all();
                if (isset($allCommands['telescope:clear'])) {
                    Artisan::call('telescope:clear', ['--all' => true]);
                } elseif (isset($allCommands['telescope:prune'])) {
                    Artisan::call('telescope:prune', ['--hours' => 0]);
                } else {
                    DB::table('telescope_entries')->delete();
                }
            } elseif ($table === 'activity_log') {
                // Agresivnější čištění pro activity_log z KPI (7 dní místo 30)
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('created_at', '<', now()->subDays(7))->delete();
                }
            } elseif ($table === 'external_import_logs' || $table === 'new_external_import_logs') {
                // Smazat vše starší než 2 dny (agresivnější) nebo truncate pokud je to extra velké?
                // Uživatel si stěžoval, že to nejde vyčistit a počet zůstává stejný.
                // Zkusíme truncate pokud je to specifikováno přes speciální parametr nebo prostě smazat víc.
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            } elseif (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }

            Cache::forget('system_console_kpi_data');

            Notification::make()
                ->title(__('admin/system-console.diagnostics.kpi.actions.table_pruned', ['table' => $table]))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Chyba při mazání tabulky')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function fixAllStuckImports(): void
    {
        $count = ExternalImportRun::where('status', 'running')
            ->where('updated_at', '<', now()->subMinutes(15))
            ->update([
                'status' => 'failed',
                'error_summary' => 'Bulk terminated from System Console (Stuck detection).',
                'finished_at' => now(),
            ]);

        Cache::forget('system_console_kpi_data');

        Notification::make()
            ->title(__('admin/system-console.diagnostics.kpi.actions.bulk_imports_fixed', ['count' => $count]))
            ->success()
            ->send();
    }

    public function killAllArtisanProcesses(): void
    {
        $count = 0;
        if (function_exists('shell_exec')) {
            $psOutput = shell_exec('ps aux | grep artisan | grep -v grep');
            if ($psOutput) {
                $lines = explode("\n", trim($psOutput));
                foreach ($lines as $line) {
                    $parts = preg_split('/\s+/', trim($line));
                    if (count($parts) >= 11) {
                        $pid = $parts[1];
                        shell_exec("kill -9 $pid");
                        $count++;
                    }
                }
            }
        }

        Cache::forget('system_console_kpi_data');

        Notification::make()
            ->title(__('admin/system-console.diagnostics.kpi.actions.bulk_processes_killed', ['count' => $count]))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('systemCheck')
                ->label(__('admin/system-console.actions.system_check'))
                ->icon('heroicon-m-magnifying-glass-circle')
                ->color('info')
                ->action(fn () => $this->runSystemCheck()),
        ];
    }

    protected function getCommandGroups(): array
    {
        $isLocal = app()->isLocal();
        $seeders = [];
        if (is_dir(database_path('seeders'))) {
            $files = scandir(database_path('seeders'));
            foreach ($files as $file) {
                if (str_ends_with($file, '.php')) {
                    $seeders[] = str_replace('.php', '', $file);
                }
            }
        }

        // Týmy pro select
        $teams = Team::orderBy('name')->get();
        $teamOptions = ['all' => __('admin/system-console.commands.stats_sync_team.selects.team.all')];
        foreach ($teams as $team) {
            $teamOptions[$team->slug] = $team->name;
        }

        // Sezóny pro select
        $seasons = Season::orderBy('name', 'desc')->get();
        $seasonOptions = ['all' => __('admin/system-console.commands.stats_sync_team.selects.season.all')];
        foreach ($seasons as $season) {
            $seasonOptions[$season->name] = $season->name;
        }

        // Uživatelé pro select (pouze ti s externím mapováním nebo všichni?)
        // Vzhledem k tomu, že stats:sync-players syncuje s cz.basketball, je dobré nabídnout ty, co mají mapping.
        // Ale user_id může být kdokoliv, kdo má profil.
        $users = User::query()
            ->orderBy('id', 'desc')
            ->get()
            ->mapWithKeys(fn ($user) => [$user->id => ($user->last_name . ' ' . $user->first_name ?: $user->name) . " (#{$user->id})"])
            ->toArray();
        $userOptions = ['' => '-- Všichni uživatelé --'] + $users;

        $groups = [];

        // 1. AI & Vyhledávání (Vždy)
        $groups[__('admin/system-console.groups.ai')] = [
            'ai:index' => [
                'label' => __('admin/system-console.commands.ai_index.label'),
                'desc' => __('admin/system-console.commands.ai_index.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--locale=all' => __('admin/system-console.commands.ai_index.flags.all'),
                    '--locale=cs' => __('admin/system-console.commands.ai_index.flags.cs'),
                    '--locale=en' => __('admin/system-console.commands.ai_index.flags.en'),
                    '--section=frontend' => 'Sekce: Frontend',
                    '--section=member' => 'Sekce: Member',
                    '--section=admin' => 'Sekce: Admin',
                    '--fresh' => __('admin/system-console.commands.ai_index.flags.fresh'),
                    '--enrich' => __('admin/system-console.commands.ai_index.flags.enrich'),
                    '--no-ai' => 'Jen standardní hledání (bez AI)',
                    '--no-interaction' => __('admin/system-console.commands.ai_index.flags.no_interaction'),
                ],
                'color' => 'primary',
                'icon' => FilamentIcon::get(AppIcon::AI),
            ],
        ];

        // 2. Správa & Nasazení (Pouze LOCALLY)
        if ($isLocal) {
            $groups[__('admin/system-console.groups.deploy')] = [
                'app:deploy' => [
                    'label' => __('admin/system-console.commands.deploy.label'),
                    'desc' => __('admin/system-console.commands.deploy.desc'),
                    'type' => 'artisan',
                    'color' => 'success',
                    'icon' => FilamentIcon::get(AppIcon::ROCKET),
                ],
                'app:sync' => [
                    'label' => __('admin/system-console.commands.sync.label'),
                    'desc' => __('admin/system-console.commands.sync.desc'),
                    'type' => 'artisan',
                    'color' => 'warning',
                    'icon' => FilamentIcon::get(AppIcon::REFRESH),
                ],
                'app:local:prepare' => [
                    'label' => __('admin/system-console.commands.local_prepare.label'),
                    'desc' => __('admin/system-console.commands.local_prepare.desc'),
                    'type' => 'artisan',
                    'color' => 'info',
                    'icon' => FilamentIcon::get(AppIcon::UPLOAD),
                ],
                'app:production:setup' => [
                    'label' => __('admin/system-console.commands.prod_setup.label'),
                    'desc' => __('admin/system-console.commands.prod_setup.desc'),
                    'type' => 'artisan',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get(AppIcon::GEARS),
                ],
            ];
        }

        // 3. Synchronizace dat (Vždy)
        $groups[__('admin/system-console.groups.sync')] = [
            'app:icons:sync' => [
                'label' => __('admin/system-console.commands.icons_sync.label'),
                'desc' => __('admin/system-console.commands.icons_sync.desc'),
                'type' => 'artisan',
                'flags' => ['--pro' => __('admin/system-console.commands.icons_sync.flags.pro')],
                'color' => 'primary',
                'icon' => FilamentIcon::get(AppIcon::MEDIA_LIBRARY),
            ],
            'app:icons:doctor' => [
                'label' => __('admin/system-console.commands.icons_doctor.label'),
                'desc' => __('admin/system-console.commands.icons_doctor.desc'),
                'type' => 'artisan',
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::STETHOSCOPE),
            ],
            'announcements:sync' => [
                'label' => __('admin/system-console.commands.announcements_sync.label'),
                'desc' => __('admin/system-console.commands.announcements_sync.desc'),
                'type' => 'artisan',
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::ANNOUNCEMENTS),
            ],
            'finance:sync' => [
                'label' => __('admin/system-console.commands.finance_sync.label'),
                'desc' => __('admin/system-console.commands.finance_sync.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--fresh' => __('admin/system-console.commands.finance_sync.flags.--fresh'),
                    '--import' => __('admin/system-console.commands.finance_sync.flags.--import'),
                    '--force' => __('admin/system-console.commands.finance_sync.flags.--force'),
                ],
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::FINANCE_PAYMENTS),
            ],
            'finance:cleanup' => [
                'label' => __('admin/system-console.commands.finance_cleanup.label'),
                'desc' => __('admin/system-console.commands.finance_cleanup.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--force' => __('admin/system-console.commands.finance_cleanup.flags.--force'),
                ],
                'color' => 'danger',
                'icon' => FilamentIcon::get(AppIcon::BROOM_WIDE),
            ],
            'stats:sync-players' => [
                'label' => __('admin/system-console.commands.stats_sync_players.label'),
                'desc' => __('admin/system-console.commands.stats_sync_players.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--excesive' => __('admin/system-console.commands.stats_sync_players.flags.excesive'),
                    '--force' => __('admin/system-console.commands.stats_sync_players.flags.force'),
                ],
                'selects' => [
                    [
                        'name' => '--team_id',
                        'label' => __('admin/system-console.commands.stats_sync_players.team_filter_label'),
                        'options' => array_merge(['' => __('admin/system-console.commands.stats_sync_players.all_teams')], Team::orderBy('name')->pluck('name', 'id')->toArray()),
                    ],
                    [
                        'name' => '--user_id',
                        'label' => __('admin/system-console.commands.stats_sync_players.input_label'),
                        'options' => $userOptions,
                        'searchable' => true,
                    ],
                ],
                'color' => 'primary',
                'icon' => FilamentIcon::get(AppIcon::USERS),
            ],
            'stats:sync-standings' => [
                'label' => __('admin/system-console.commands.stats_sync_standings.label'),
                'desc' => __('admin/system-console.commands.stats_sync_standings.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--force' => __('admin/system-console.commands.stats_sync_standings.flags.force'),
                ],
                'selects' => [
                    [
                        'name' => 'seasonNameOrId',
                        'label' => __('admin/system-console.commands.stats_sync_standings.selects.season.label'),
                        'options' => [
                            '' => __('admin/system-console.commands.stats_sync_standings.selects.season.active'),
                            ...$seasonOptions,
                        ],
                    ],
                ],
                'color' => 'success',
                'icon' => 'heroicon-m-table-cells',
            ],
            'stats:sync-team-season' => [
                'label' => __('admin/system-console.commands.stats_sync_team.label'),
                'desc' => __('admin/system-console.commands.stats_sync_team.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--excesive' => __('admin/system-console.commands.stats_sync_team.flags.excesive'),
                    '--sync' => __('admin/system-console.commands.stats_sync_team.flags.sync'),
                    '--force' => __('admin/system-console.commands.stats_sync_team.flags.force'),
                ],
                'selects' => [
                    [
                        'name' => 'teamSlug',
                        'label' => __('admin/system-console.commands.stats_sync_team.selects.team.label'),
                        'options' => $teamOptions,
                    ],
                    [
                        'name' => 'seasonNameOrId',
                        'label' => __('admin/system-console.commands.stats_sync_team.selects.season.label'),
                        'options' => $seasonOptions,
                    ],
                ],
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::MATCHES),
            ],
            'stats:import' => [
                'label' => __('admin/system-console.commands.stats_import.label'),
                'desc' => __('admin/system-console.commands.stats_import.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--recent' => __('admin/system-console.commands.stats_import.flags.recent'),
                ],
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::DASHBOARD),
            ],
            'stats:predictions:recompute' => [
                'label' => __('admin/system-console.commands.stats_predictions_recompute.label'),
                'desc' => __('admin/system-console.commands.stats_predictions_recompute.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--all' => __('admin/system-console.commands.stats_predictions_recompute.flags.--all'),
                ],
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::AI),
            ],
        ];

        // 4. Data ze starého systému (Vždy)
        $groups[__('admin/system-console.groups.legacy')] = [
            'app:legacy:sync' => [
                'label' => __('admin/system-console.commands.legacy_sync.label'),
                'desc' => __('admin/system-console.commands.legacy_sync.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--fresh' => __('admin/system-console.commands.legacy_sync.flags.--fresh'),
                    '--users' => __('admin/system-console.commands.legacy_sync.flags.--users'),
                ],
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::DATABASE),
            ],
            'app:attendance:sync' => [
                'label' => __('admin/system-console.commands.legacy_attendance_sync.label'),
                'desc' => __('admin/system-console.commands.legacy_attendance_sync.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--fresh' => __('admin/system-console.commands.legacy_attendance_sync.flags.--fresh'),
                ],
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::EVENTS),
            ],
        ];

        // 4. Údržba & Čištění (Vždy)
        $groups[__('admin/system-console.groups.maintenance')] = [
            'system:cleanup' => [
                'label' => __('admin/system-console.commands.system_cleanup.label'),
                'desc' => __('admin/system-console.commands.system_cleanup.desc'),
                'type' => 'artisan',
                'color' => 'danger',
                'icon' => FilamentIcon::get(AppIcon::BROOM_WIDE),
            ],
            'app:finance-mark-past-seasons-paid' => [
                'label' => __('admin/system-console.commands.finance_archive.label'),
                'desc' => __('admin/system-console.commands.finance_archive.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--dry-run' => __('admin/system-console.commands.finance_archive.flags.--dry-run'),
                    '--force' => __('admin/system-console.commands.finance_sync.flags.--force'),
                ],
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::ARCHIVE),
            ],
            'audit:cleanup' => [
                'label' => __('admin/system-console.commands.audit_cleanup.label'),
                'desc' => __('admin/system-console.commands.audit_cleanup.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--days=30' => __('admin/system-console.commands.audit_cleanup.flags.30'),
                    '--days=90' => __('admin/system-console.commands.audit_cleanup.flags.90'),
                    '--days=180' => __('admin/system-console.commands.audit_cleanup.flags.180'),
                ],
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::AUDIT),
            ],
            'club:backfill-identifiers' => [
                'label' => __('admin/system-console.commands.backfill_ids.label'),
                'desc' => __('admin/system-console.commands.backfill_ids.desc'),
                'type' => 'artisan',
                'flags' => ['--regenerate-existing' => __('admin/system-console.commands.backfill_ids.flags.regenerate')],
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::ATTENDANCE),
            ],
            'rsvp:reminders' => [
                'label' => __('admin/system-console.commands.rsvp_reminders.label'),
                'desc' => __('admin/system-console.commands.rsvp_reminders.desc'),
                'type' => 'artisan',
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::BELL),
            ],
            'telescope:clear' => [
                'label' => __('admin/system-console.commands.telescope_clear.label'),
                'desc' => __('admin/system-console.commands.telescope_clear.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--all' => 'Smazat ÚPLNĚ VŠECHNY záznamy (nejen starší 24h)',
                ],
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::TRASH),
            ],
        ];

        // 5. Databáze (Vždy)
        $groups[__('admin/system-console.groups.database')] = [
            'migrate' => [
                'label' => __('admin/system-console.commands.migrate.label'),
                'desc' => __('admin/system-console.commands.migrate.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--force' => __('admin/system-console.commands.migrate.flags.force'),
                    '--seed' => __('admin/system-console.commands.migrate.flags.seed'),
                ],
                'color' => 'primary',
                'icon' => FilamentIcon::get(AppIcon::DATABASE),
            ],
            'migrate:rollback' => [
                'label' => __('admin/system-console.commands.migrate_rollback.label'),
                'desc' => __('admin/system-console.commands.migrate_rollback.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--force' => __('admin/system-console.commands.migrate_rollback.flags.force'),
                    '--step=1' => __('admin/system-console.commands.migrate_rollback.flags.step'),
                ],
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::UNDO),
            ],
            'db:seed' => [
                'label' => __('admin/system-console.commands.db_seed.label'),
                'desc' => __('admin/system-console.commands.db_seed.desc'),
                'type' => 'artisan',
                'flags' => ['--force' => __('admin/system-console.commands.db_seed.flags.force')],
                'select' => [
                    'name' => '--class',
                    'label' => __('admin/system-console.commands.db_seed.select_label'),
                    'options' => array_combine($seeders, $seeders),
                ],
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::SEEDLING),
            ],
            'app:seed' => [
                'label' => __('admin/system-console.commands.app_seed.label'),
                'desc' => __('admin/system-console.commands.app_seed.desc'),
                'type' => 'artisan',
                'flags' => ['--fresh' => __('admin/system-console.commands.app_seed.flags.fresh')],
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::SEEDLING),
            ],
        ];

        // 6. Optimalizace & Cache (Vždy)
        $groups[__('admin/system-console.groups.optimization')] = [
            'optimize' => [
                'label' => __('admin/system-console.commands.optimize.label'),
                'desc' => __('admin/system-console.commands.optimize.desc'),
                'type' => 'artisan',
                'color' => 'success',
                'icon' => FilamentIcon::get(AppIcon::BOLT),
            ],
            'optimize:cache' => [
                'label' => __('admin/system-console.commands.optimize_cache.label'),
                'desc' => __('admin/system-console.commands.optimize_cache.desc'),
                'type' => 'artisan',
                'color' => 'success',
                'icon' => FilamentIcon::get(AppIcon::BOLT),
            ],
            'optimize:clear' => [
                'label' => __('admin/system-console.commands.optimize_clear.label'),
                'desc' => __('admin/system-console.commands.optimize_clear.desc'),
                'type' => 'artisan',
                'color' => 'danger',
                'icon' => FilamentIcon::get(AppIcon::TRASH),
            ],
            'page-cache:prime' => [
                'label' => __('admin/system-console.commands.page_cache_prime.label'),
                'desc' => __('admin/system-console.commands.page_cache_prime.desc'),
                'type' => 'artisan',
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::REFRESH),
            ],
            'page-cache:clear' => [
                'label' => __('admin/system-console.commands.page_cache_clear.label'),
                'desc' => __('admin/system-console.commands.page_cache_clear.desc'),
                'type' => 'artisan',
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::TRASH),
            ],
            'config:cache' => [
                'label' => __('admin/system-console.commands.config_cache.label'),
                'desc' => __('admin/system-console.commands.config_cache.desc'),
                'type' => 'internal',
                'color' => 'primary',
                'icon' => FilamentIcon::get(AppIcon::SETTINGS),
            ],
            'route:cache' => [
                'label' => __('admin/system-console.commands.route_cache.label'),
                'desc' => __('admin/system-console.commands.route_cache.desc'),
                'type' => 'internal',
                'color' => 'primary',
                'icon' => FilamentIcon::get(AppIcon::ROUTE),
            ],
            'view:cache' => [
                'label' => __('admin/system-console.commands.view_cache.label'),
                'desc' => __('admin/system-console.commands.view_cache.desc'),
                'type' => 'internal',
                'color' => 'primary',
                'icon' => FilamentIcon::get(AppIcon::VIEW),
            ],
            'storage:link' => [
                'label' => __('admin/system-console.commands.storage_link.label'),
                'desc' => __('admin/system-console.commands.storage_link.desc'),
                'type' => 'internal',
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::LINK),
            ],
            'storage:repair' => [
                'label' => __('admin/system-console.commands.storage_repair.label'),
                'desc' => __('admin/system-console.commands.storage_repair.desc'),
                'type' => 'internal',
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::WRENCH),
            ],
        ];

        // 7. Vývojářské nástroje (Filtrované)
        $devTools = [
            'composer install' => [
                'label' => __('admin/system-console.commands.composer_install.label'),
                'desc' => __('admin/system-console.commands.composer_install.desc'),
                'type' => 'shell',
                'flags' => [
                    '--no-dev' => __('admin/system-console.commands.composer_install.flags.no_dev'),
                    '--optimize-autoloader' => __('admin/system-console.commands.composer_install.flags.optimize'),
                ],
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::PACKAGE),
            ],
            'npm install' => [
                'label' => __('admin/system-console.commands.npm_install.label'),
                'desc' => __('admin/system-console.commands.npm_install.desc'),
                'type' => 'shell',
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::DOWNLOAD),
            ],
            'npm run build' => [
                'label' => __('admin/system-console.commands.npm_build.label'),
                'desc' => __('admin/system-console.commands.npm_build.desc'),
                'type' => 'shell',
                'color' => 'success',
                'icon' => FilamentIcon::get(AppIcon::HAMMER),
            ],
        ];

        if (! $isLocal) {
            $devTools['git status'] = [
                'label' => __('admin/system-console.commands.git_status.label'),
                'desc' => __('admin/system-console.commands.git_status.desc'),
                'type' => 'shell',
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::BRANCH),
            ];
            $devTools['git pull'] = [
                'label' => __('admin/system-console.commands.git_pull.label'),
                'desc' => __('admin/system-console.commands.git_pull.desc'),
                'type' => 'shell',
                'color' => 'warning',
                'icon' => FilamentIcon::get(AppIcon::STAT_SOURCES),
            ];
        }

        $groups[__('admin/system-console.groups.dev_tools')] = $devTools;

        // 8. Diagnostika (Vždy)
        $groups[__('admin/system-console.groups.diagnostics')] = [
            'system:check' => [
                'label' => 'System Check (Detailed)',
                'desc' => 'Komplexní diagnostika serveru, binárek a oprávnění.',
                'type' => 'artisan',
                'color' => 'success',
                'icon' => FilamentIcon::get(AppIcon::STETHOSCOPE),
            ],
            'php:basic' => [
                'label' => 'PHP: Základní info',
                'desc' => 'Verze PHP, SAPI, uživatel a webová binárka.',
                'type' => 'internal',
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::INFO),
            ],
            'php:ini' => [
                'label' => 'PHP: Konfigurace (INI)',
                'desc' => 'Limity a omezení PHP (disable_functions, open_basedir).',
                'type' => 'internal',
                'color' => 'info',
                'icon' => FilamentIcon::get(AppIcon::SLIDERS),
            ],
            'php -v' => [
                'label' => 'PHP CLI Version',
                'desc' => 'Zobrazí verzi PHP v systémovém shellu.',
                'type' => 'shell',
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::PHP, 'fab'),
            ],
            'node -v' => [
                'label' => 'Node Version',
                'desc' => 'Zobrazí verzi Node.js na serveru.',
                'type' => 'shell',
                'color' => 'gray',
                'icon' => FilamentIcon::get(AppIcon::NODE_JS, 'fab'),
            ],
        ];

        // Automatické přidání can_be_internal pro všechny Artisan příkazy
        foreach ($groups as &$cmds) {
            foreach ($cmds as &$config) {
                if ($config['type'] === 'artisan') {
                    $config['can_be_internal'] = true;
                }
            }
        }

        return $groups;
    }

    public function run(string $command, string $type, array $selectedFlags = [], ?string $selectName = null, mixed $selectValue = null, bool $useInternal = false): void
    {
        if ($command === 'system:check') {
            $this->runSystemCheck();

            return;
        }

        if ($type === 'internal' || ($useInternal && $type === 'artisan')) {
            $this->runInternal($command, $selectedFlags, $selectName, $selectValue);

            return;
        }

        set_time_limit(0);
        $timestamp = now()->format('H:i:s');

        $valueStr = '';
        if ($selectValue) {
            if (is_array($selectValue)) {
                $valueStr = ' ' . implode(' ', array_filter($selectValue));
            } else {
                $valueStr = " $selectValue";
            }
        }

        $this->safelyStream(content: "\n[$timestamp] > $command".(empty($selectedFlags) ? '' : ' '.implode(' ', $selectedFlags)).$valueStr."\n", replace: false);

        try {
            if ($type === 'artisan') {
                $phpBinary = BinaryHelper::getPhpBinary();

                $this->streamDebugInfo($phpBinary, 'artisan');

                // Sestavení commandu jako POLE pro Symfony Process (obchází /bin/sh)
                $commandArray = [$phpBinary, 'artisan', $command, '--no-interaction'];
                foreach ($selectedFlags as $flag) {
                    $commandArray[] = $flag;
                }

                if (is_array($selectValue)) {
                    foreach ($selectValue as $name => $val) {
                        if (empty($val)) continue;
                        if (str_starts_with($name, '--')) {
                            $commandArray[] = "$name=$val";
                        } else {
                            $commandArray[] = $val;
                        }
                    }
                } elseif ($selectName && $selectValue) {
                    if (str_starts_with($selectName, '--')) {
                        $commandArray[] = "$selectName=$selectValue";
                    } else {
                        $commandArray[] = $selectValue;
                    }
                }

                $this->executeRealtime($commandArray);
                $success = true;
            } else {
                $commandArray = $this->parseCommandToArray($command);

                // Mapování binárek na cesty (inteligentní finder + .env override)
                $binaryMap = [
                    'php' => BinaryHelper::getPhpBinary(),
                    'node' => BinaryHelper::getNodeBinary(),
                    'npm' => BinaryHelper::getNpmBinary(),
                    'composer' => 'composer',
                    'git' => 'git',
                ];

                $binaryPath = $commandArray[0];
                if (isset($commandArray[0]) && isset($binaryMap[$commandArray[0]])) {
                    $commandArray[0] = $binaryMap[$commandArray[0]];
                    $binaryPath = $commandArray[0];
                }

                // Přidání vlajek (flags) k shell příkazu
                foreach ($selectedFlags as $flag) {
                    $commandArray[] = $flag;
                }

                if ($selectName && $selectValue) {
                    $commandArray[] = "$selectName=$selectValue";
                }

                $this->streamDebugInfo($binaryPath, 'shell');
                $this->executeRealtime($commandArray);
                $success = true;
            }

            Notification::make()
                ->title($success ? __('admin/system-console.notifications.completed') : __('admin/system-console.notifications.failed'))
                ->status($success ? 'success' : 'danger')
                ->send();

        } catch (\Throwable $e) {
            $this->safelyStream(content: "\nCHYBA: ".$e->getMessage(), replace: false);
            Log::error('SystemConsole Error: '.$e->getMessage(), [
                'command' => $command,
                'type' => $type,
                'exception' => $e,
            ]);

            Notification::make()
                ->title(__('admin/system-console.notifications.execution_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function runInternal(string $command, array $flags = [], ?string $selectName = null, mixed $selectValue = null): void
    {
        set_time_limit(0);
        @ini_set('memory_limit', '512M');
        @ignore_user_abort(true);
        $timestamp = now()->format('H:i:s');

        $valueStr = '';
        if ($selectValue) {
            if (is_array($selectValue)) {
                $valueStr = ' ' . implode(' ', array_filter($selectValue));
            } else {
                $valueStr = " $selectValue";
            }
        }

        $internalDebug = "";
        if (config('app.debug')) {
            $internalDebug .= "[INTERNAL DEBUG] Memory Limit: " . ini_get('memory_limit') . "\n";
            $internalDebug .= "[INTERNAL DEBUG] Time Limit: " . ini_get('max_execution_time') . "\n";
            $internalDebug .= "[INTERNAL DEBUG] DB Connection: " . config('database.default') . "\n";
        }

        $this->safelyStream(content: "\n[$timestamp] > (Internal) artisan $command".(empty($flags) ? '' : ' '.implode(' ', $flags)).$valueStr."\n" . $internalDebug, replace: false);

        try {
            $parameters = ['--no-interaction' => true];
            foreach ($flags as $flag) {
                if (str_contains($flag, '=')) {
                    [$key, $value] = explode('=', $flag, 2);
                    $parameters[$key] = $value;
                } else {
                    $parameters[$flag] = true;
                }
            }

            if (is_array($selectValue)) {
                foreach ($selectValue as $name => $val) {
                    if (empty($val)) continue;
                    // Pro Artisan::call se -- u options dává jako název klíče
                    $parameters[$name] = $val;
                }
            } elseif ($selectName && $selectValue) {
                $parameters[$selectName] = $selectValue;
            }

            // Podpora pro diagnostické interní příkazy (jako v kalkulačce)
            if ($command === 'php:basic' || $command === 'php:ini' || $command === 'storage:repair') {
                $output = '';
                if ($command === 'php:basic') {
                    $output .= 'PHP Version: '.PHP_VERSION."\n";
                    $output .= 'PHP SAPI: '.php_sapi_name()."\n";
                    $output .= 'PHP Binary: '.PHP_BINARY."\n";
                    $output .= 'Current User: '.get_current_user().' (UID: '.(function_exists('posix_getuid') ? posix_getuid() : 'N/A').")\n";
                    $output .= 'OS: '.PHP_OS."\n";
                    $output .= 'CWD: '.getcwd()."\n";
                    $output .= 'CWD Writeable: '.(is_writable(getcwd()) ? 'Yes' : 'No')."\n";
                } elseif ($command === 'php:ini') {
                    $output .= 'disable_functions: '.(ini_get('disable_functions') ?: '(none)')."\n";
                    $output .= 'open_basedir: '.(ini_get('open_basedir') ?: '(none)')."\n";
                    $output .= 'memory_limit: '.ini_get('memory_limit')."\n";
                    $output .= 'max_execution_time: '.ini_get('max_execution_time')."\n";
                    $output .= 'safe_mode: '.(ini_get('safe_mode') ? 'On' : 'Off')."\n";
                } elseif ($command === 'storage:repair') {
                    $publicStorage = public_path('storage');
                    $target = storage_path('app/public');

                    $output .= "Vynucená oprava symlinků pro Webglobe hosting...\n";
                    $output .= "Cíl: $target\n";
                    $output .= "Link: $publicStorage\n";

                    if (file_exists($publicStorage)) {
                        if (is_link($publicStorage)) {
                            $output .= "Stávající link nalezen, odstraňuji...\n";
                            @unlink($publicStorage);
                        } else {
                            $output .= "VAROVÁNÍ: Na místě linku existuje skutečný adresář! Přejmenovávám na storage_old...\n";
                            @rename($publicStorage, $publicStorage.'_old_'.time());
                        }
                    }

                    if (@symlink($target, $publicStorage)) {
                        $output .= "✅ Symlink úspěšně vytvořen v reálném veřejném adresáři.\n";
                    } else {
                        $output .= "❌ CHYBA: Symlink se nepodařilo vytvořit. Zkuste to přes SSH nebo zkontrolujte oprávnění.\n";
                    }

                    // Kontrola uploads
                    $uploadsPath = public_path('uploads');
                    $output .= "\nKontrola uploads adresáře: $uploadsPath\n";
                    if (! is_dir($uploadsPath)) {
                        if (@mkdir($uploadsPath, 0775, true)) {
                            $output .= "✅ Adresář uploads vytvořen.\n";
                        } else {
                            $output .= "❌ CHYBA: Nepodařilo se vytvořit adresář uploads.\n";
                        }
                    } else {
                        $output .= "✅ Adresář uploads již existuje.\n";
                    }
                }

                $this->safelyStream(content: $output, replace: false);

                Notification::make()
                    ->title(__('admin/system-console.notifications.completed'))
                    ->success()
                    ->send();

                return;
            }

            // Použijeme BufferedOutput pro zachycení výstupu a budeme ho streamovat
            // Poznámka: Artisan::call je synchronní, takže streamování proběhne až PO dokončení,
            // pokud nepoužijeme vlastní Output třídu, která volá $this->stream().
            $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput;

            Artisan::call($command, $parameters, $outputBuffer);
            $result = $outputBuffer->fetch();

            $this->safelyStream(content: $result, replace: false);

            Notification::make()
                ->title(__('admin/system-console.notifications.completed'))
                ->success()
                ->send();

            // Pokud příkaz měnil integritu cache, vynutíme refresh stránky po krátkém zpoždění,
            // aby Livewire dostal čerstvý snapshot a předešli jsme chybě "Undefined array key children"
            if (in_array($command, ['optimize', 'optimize:cache', 'optimize:clear', 'page-cache:clear', 'cache:clear'])) {
                $this->js('setTimeout(() => window.location.reload(), 2000)');
            }
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $stackTrace = $e->getTraceAsString();

            $fatalError = "\nFATAL ERROR: ".$errorMessage;
            if (config('app.debug')) {
                $fatalError .= "\n\nStack Trace:\n".substr($stackTrace, 0, 1000).'...';
            }
            $this->safelyStream(content: $fatalError, replace: false);

            Log::error('SystemConsole Internal Error: '.$errorMessage, [
                'command' => $command,
                'flags' => $flags,
                'exception' => $e,
            ]);

            Notification::make()
                ->title(__('admin/system-console.notifications.execution_error'))
                ->body($errorMessage)
                ->danger()
                ->send();
        }
    }

    protected function runSystemCheck(): void
    {
        $timestamp = now()->format('H:i:s');
        $this->safelyStream(content: "\n[$timestamp] > System Check (Detailed Diagnostic)\n", replace: false);

        $out = "\n".str_repeat('=', 60)."\n";
        $out .= "         SYSTÉMOVÁ DIAGNOSTIKA (KBELŠTÍ SOKOLI)\n";
        $out .= str_repeat('=', 60)."\n\n";

        // 1. ZÁKLADNÍ PROSTŘEDÍ
        $out .= "--- [1] PROSTŘEDÍ A UŽIVATEL ---\n";
        $user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user();
        $uid = function_exists('posix_getuid') ? posix_getuid() : 'Neznámé';
        $gid = function_exists('posix_getgid') ? posix_getgid() : 'Neznámé';

        $out .= sprintf("%-25s: %s\n", 'Aktuální uživatel', $user);
        $out .= sprintf("%-25s: UID: %s, GID: %s\n", 'Identita', $uid, $gid);
        $out .= sprintf("%-25s: %s\n", 'Operační systém', PHP_OS);
        $out .= sprintf("%-25s: %s\n", 'PHP Verze (Web)', PHP_VERSION);
        $out .= sprintf("%-25s: %s\n", 'PHP Binary (Web)', PHP_BINARY);
        $out .= sprintf("%-25s: %s\n", 'Adresář aplikace', base_path());
        $out .= sprintf("%-25s: %s\n", 'Veřejný adresář', public_path());
        $out .= sprintf("%-25s: %s\n", 'Storage adresář', storage_path());
        $out .= sprintf("%-25s: %s\n", 'PATH', getenv('PATH') ?: '(není v env)');
        $out .= sprintf("%-25s: %s\n", 'APP_URL', config('app.url'));
        $out .= sprintf("%-25s: %s (%s)\n", 'Uploads Disk', config('filesystems.uploads.disk'), config('filesystems.uploads.dir'));
        $out .= "\n";

        // 2. OMEZENÍ PHP
        $out .= "--- [2] OMEZENÍ A FUNKCE PHP ---\n";
        $disabled = ini_get('disable_functions') ?: '(žádné)';
        $out .= sprintf("%-25s: %s\n", 'Zakázané funkce', $disabled);
        $out .= sprintf("%-25s: %s\n", 'open_basedir', ini_get('open_basedir') ?: '(neomezeno)');
        $out .= sprintf("%-25s: %s\n", 'memory_limit', ini_get('memory_limit'));
        $out .= sprintf("%-25s: %s\n", 'max_execution_time', ini_get('max_execution_time').'s');

        $criticalFunctions = ['proc_open', 'proc_terminate', 'proc_get_status', 'proc_close', 'shell_exec', 'exec', 'system', 'passthru'];
        foreach ($criticalFunctions as $func) {
            $status = function_exists($func) ? 'Dostupná' : '!!! CHYBÍ / ZAKÁZÁNA !!!';
            $out .= sprintf("%-25s: %s\n", $func, $status);
        }
        $out .= "\n";

        // 3. SOUBORY A OPRÁVNĚNÍ
        $out .= "--- [3] SOUBORY A OPRÁVNĚNÍ ---\n";

        // Kontrola linku storage
        $storageLink = public_path('storage');
        if (file_exists($storageLink)) {
            $isLink = is_link($storageLink);
            $target = $isLink ? readlink($storageLink) : 'Není link (je to adresář)';
            $out .= sprintf("%-25s: Existuje (%s -> %s)\n", 'Storage link', $isLink ? 'Link' : 'Adresář', $target);
        } else {
            $out .= sprintf("%-25s: Neexistuje ❌\n", 'Storage link');
        }

        // Kontrola uploads složky
        $uploadsPath = public_path('uploads');
        if (file_exists($uploadsPath)) {
            $isLink = is_link($uploadsPath);
            $target = $isLink ? readlink($uploadsPath) : 'Adresář';
            $out .= sprintf("%-25s: Existuje (%s)\n", 'Uploads složka', $target);
        } else {
            $out .= sprintf("%-25s: Neexistuje ❌ (Vytvoří se při prvním uploadu)\n", 'Uploads složka');
        }

        $artisanPath = base_path('artisan');
        if (file_exists($artisanPath)) {
            $perms = substr(sprintf('%o', fileperms($artisanPath)), -4);
            $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($artisanPath))['name'] : fileowner($artisanPath);
            $out .= sprintf("%-25s: Existuje (Oprávnění: %s, Vlastník: %s)\n", 'Soubor artisan', $perms, $owner);
            if (! is_executable($artisanPath)) {
                $out .= "!!! VAROVÁNÍ: Soubor artisan není nastaven jako spustitelný (chmod +x) !!!\n";
            }
        } else {
            $out .= '!!! CHYBA: Soubor artisan nebyl nalezen v '.base_path()." !!!\n";
        }
        $out .= "\n";

        // 4. HLEDÁNÍ BINÁREK (PHP, NODE, NPM)
        $out .= "--- [4] BINÁRKY (PHP, NODE, NPM) ---\n";
        $out .= sprintf("%-25s: %s\n", 'PHP (BinaryHelper)', BinaryHelper::getPhpBinary());
        $out .= sprintf("%-25s: %s\n", 'Node.js (BinaryHelper)', BinaryHelper::getNodeBinary());
        $out .= sprintf("%-25s: %s\n", 'NPM (BinaryHelper)', BinaryHelper::getNpmBinary());
        $out .= sprintf("%-25s: %s\n", 'FONTAWESOME_TOKEN', config('app.fontawesome_token') ? 'Nastaven ('.substr(config('app.fontawesome_token'), 0, 4).'...) ✅' : 'Chybí ❌');
        $out .= "\n";

        $potentialBinaries = [
            PHP_BINARY,
            'php8.4',
            'php8.3',
            'php8.2',
            'php8.1',
            'php',
            '/usr/bin/php8.4',
            '/usr/bin/php8.3',
            '/usr/bin/php8.2',
            '/usr/bin/php8.1',
            '/usr/bin/php',
            '/usr/local/bin/php8.4',
            '/usr/local/bin/php8.3',
            '/usr/local/bin/php',
            '/opt/php84/bin/php', // Časté cesty na Webglobe/hostingu
            '/opt/php8.4/bin/php',
            '/usr/bin/env php',
        ];

        // Zkusíme 'which' pro každou krátkou binárku
        if (function_exists('shell_exec')) {
            $shorts = ['php8.4', 'php8.3', 'php', 'php84', 'php83'];
            foreach ($shorts as $s) {
                $path = trim((string) shell_exec("which $s"));
                if ($path && ! in_array($path, $potentialBinaries)) {
                    $potentialBinaries[] = $path;
                }
            }
        }

        $foundAny = false;
        $bestBinary = null;
        $bestBinaryScore = 0;

        foreach (array_unique($potentialBinaries) as $bin) {
            $cleanBin = trim($bin, "\"'");
            $exists = false;

            // Kontrola existence (pokud je to absolutní cesta)
            if (str_starts_with($cleanBin, '/')) {
                $exists = file_exists($cleanBin);
            } else {
                // Pokud je to jen název, zkusíme 'which'
                $exists = function_exists('shell_exec') && ! empty(trim((string) shell_exec("which $cleanBin")));
            }

            if (! $exists && $cleanBin !== PHP_BINARY && ! str_contains($cleanBin, ' ')) {
                continue;
            }

            $foundAny = true;
            $isExecutable = is_executable($cleanBin) ? 'ANO' : 'NE';

            // Zkusíme spustit -v jako POLE (bez shellu)
            $versionResult = 'Chyba při spouštění';
            $modulesInfo = '';
            $score = 0;

            try {
                if (function_exists('proc_open')) {
                    $process = new Process([$cleanBin, '-v']);
                    $process->run();
                    if ($process->isSuccessful()) {
                        $versionResult = explode("\n", trim($process->getOutput()))[0];

                        // KONTROLA MODULŮ
                        $mods = $this->getPhpModules($cleanBin);
                        $features = [];
                        if ($mods['pdo']) {
                            $features[] = 'PDO';
                            $score += 10;
                        }
                        if ($mods['tokenizer']) {
                            $features[] = 'Tokenizer';
                            $score += 5;
                        }
                        if ($mods['json']) {
                            $features[] = 'JSON';
                            $score += 2;
                        }

                        // Preferujeme PHP 8.4
                        if (str_contains($versionResult, '8.4')) {
                            $score += 20;
                        } elseif (str_contains($versionResult, '8.3')) {
                            $score += 15;
                        }

                        if ($score > $bestBinaryScore && is_executable($cleanBin)) {
                            $bestBinaryScore = $score;
                            $bestBinary = $cleanBin;
                        }

                        if (! empty($features)) {
                            $modulesInfo = '  - Moduly: '.implode(', ', $features);
                        } else {
                            $modulesInfo = '  - !!! VAROVÁNÍ: Chybí PDO/Tokenizer (Artisan selže) !!!';
                        }
                    } else {
                        $versionResult = 'Selhalo (Kód: '.$process->getExitCode().') '.trim($process->getErrorOutput() ?: $process->getOutput());
                    }
                } else {
                    $versionResult = 'Nelze testovat (proc_open zakázán)';
                }
            } catch (\Throwable $e) {
                $versionResult = 'Exception: '.$e->getMessage();
            }

            $out .= "Cesta: $cleanBin\n";
            $out .= '  - Existuje: '.($exists ? 'Ano' : 'Možná (v PATH)')."\n";
            $out .= "  - Spustitelná: $isExecutable\n";
            $out .= "  - Verze (-v): $versionResult\n";
            if ($modulesInfo) {
                $out .= $modulesInfo."\n";
            }
            $out .= "\n";
        }

        if (! $foundAny) {
            $out .= "!!! NIKDE NEBYLA NALEZENA ŽÁDNÁ PHP BINÁRKA !!!\n";
        }

        if ($bestBinary) {
            $out .= ">>> DOPORUČENÁ BINÁRKA: $bestBinary <<<\n";
            $out .= ">>> (Má nejlepší skóre kompatibility a verzování)\n\n";
        }

        $out .= str_repeat('-', 60)."\n";
        $out .= "DOPORUČENÍ:\n";
        $out .= "1. Pokud binárka vrací Code 126, uživatel webu na ni nemá práva pro spouštění.\n";
        $out .= "2. Pokud je Artisan hlášen jako ne-spustitelný, zkuste 'chmod +x artisan'.\n";
        $out .= "3. Nastavte v .env: PROD_PHP_BINARY=/cesta/k/funkcni/binarce (musí mít PDO!)\n";
        $out .= "4. Nezapomeňte poté vyčistit cache: 'php artisan optimize:clear'\n";
        $out .= "5. Pokud shell selhává, použijte u Artisan příkazů volbu 'Internal Execution'.\n";
        $out .= str_repeat('=', 60)."\n";

        $this->safelyStream(content: $out, replace: false);

        Notification::make()
            ->title('Diagnostika dokončena')
            ->success()
            ->send();
    }

    protected function getPhpModules(string $binary): array
    {
        try {
            $process = new Process([trim($binary, "\"'"), '-m']);
            $process->run();
            if ($process->isSuccessful()) {
                $output = strtolower($process->getOutput());

                return [
                    'pdo' => str_contains($output, 'pdo'),
                    'tokenizer' => str_contains($output, 'tokenizer'),
                    'json' => str_contains($output, 'json'),
                ];
            }
        } catch (\Throwable $e) {
        }

        return ['pdo' => false, 'tokenizer' => false, 'json' => false];
    }

    public function refreshConsoleLogs(): void
    {
        $newContent = \App\Services\Support\ConsoleService::getContent();
        if ($this->consoleOutput !== $newContent) {
            $this->consoleOutput = $newContent;
            $this->dispatch('console-updated');
            $this->pollingInterval = '3s'; // Zrychlíme při změně
        } else {
            $this->pollingInterval = '10s'; // Zpomalíme při klidu
        }
    }

    public function clearConsoleLogs(): void
    {
        \App\Services\Support\ConsoleService::clear();
        $this->consoleOutput = '';
        Notification::make()->title('Console cleared')->success()->send();
    }

    /**
     * Bezpečné streamování výstupu do Livewire frontend-u.
     * Předchází Fatal Erroru "Call to a member function stream() on null",
     * pokud SupportStreaming hook není v danou chvíli dostupný (např. po artisan optimize).
     */
    protected function safelyStream(string $content, bool $replace = false): void
    {
        // Přidáme obsah do logu pro uchování stavu
        // Odstraníme přímé zápisy do $this->output a $this->consoleOutput jinde v kódu
        if ($replace) {
            $this->consoleOutput = $content;
            $this->output = $content;
        } else {
            $this->consoleOutput .= $content;
            $this->output .= $content;
        }

        try {
            // Livewire 3 stream() interně volá ComponentHookRegistry::getHook($this, SupportStreaming::class)
            // Pokud hook není nalezen (např. po vymazání/změně cache), HandlesStreaming trait
            // vyhodí chybu, protože se snaží volat metodu na null objektu (StreamManager::to()).
            // Zde voláme stream() s parametry a pokud selže, tiše ignorujeme - výstup je stále v $this->consoleOutput.
            // Používáme HtmlString, aby Livewire 3 streaming neescapoval HTML tagy (např. barvy z ConsoleService).
            $this->stream(to: 'consoleOutput', content: new \Illuminate\Support\HtmlString($content), replace: $replace);
        } catch (\Throwable $e) {
            // Ignorujeme chybu streamování, uživatel uvidí výstup po dokončení akce v $this->consoleOutput.
            Log::debug('SystemConsole: Streaming failed, likely due to cache change.', [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function streamDebugInfo(string $binaryPath, string $type): void
    {
        $user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user();
        $dir = base_path();
        $env = config('app.env');
        $version = $this->getBinaryVersion($binaryPath);

        $debug = "\n[DEBUG] ------------------------------------------------------------\n";
        $debug .= '[DEBUG] Akce: '.($type === 'artisan' ? 'Artisan Command' : 'Shell Command')."\n";
        $debug .= "[DEBUG] Binárka: {$binaryPath}\n";
        $debug .= "[DEBUG] Verze: {$version}\n";

        // Kontrola PDO pro Artisan
        if ($type === 'artisan') {
            $mods = $this->getPhpModules($binaryPath);
            if (! $mods['pdo'] || ! $mods['tokenizer']) {
                $debug .= "[DEBUG] !!! VAROVÁNÍ: Tato binárka postrádá PDO nebo Tokenizer !!!\n";
                $debug .= "[DEBUG] !!! Doporučujeme použít 'Internal Execution' !!!\n";
            }
        }

        $debug .= "[DEBUG] Adresář: {$dir}\n";
        $debug .= "[DEBUG] Uživatel: {$user}\n";
        $debug .= "[DEBUG] Prostředí: {$env}\n";
        $debug .= '[DEBUG] PHP limit: '.ini_get('max_execution_time')."s\n";
        $debug .= "[DEBUG] ------------------------------------------------------------\n";
        $this->safelyStream(content: $debug, replace: false);
    }

    protected function getBinaryVersion(string $binary): string
    {
        try {
            // Odstraníme případné uvozovky pro spuštění verze
            $cleanBinary = trim($binary, "\"'");
            $binaryLower = strtolower($cleanBinary);

            // Pokud je to /usr/bin/php a jsme na produkci, zkusíme zjistit, zda je spustitelný
            if ($cleanBinary === '/usr/bin/php' && config('app.env') === 'production') {
                if (! is_executable($cleanBinary)) {
                    return 'SOUBOR NENÍ SPUSTITELNÝ (Code 126 fallback)';
                }
            }

            $flag = '-v';
            if (str_contains($binaryLower, 'php')) {
                $flag = '-v';
            } elseif (str_contains($binaryLower, 'composer') || str_contains($binaryLower, 'git')) {
                $flag = '--version';
            } elseif (str_contains($binaryLower, 'npm') || str_contains($binaryLower, 'node')) {
                $flag = '-v';
            }

            // Spustíme jako POLE bez shellu pro vyšší stabilitu
            $process = new Process([$cleanBinary, $flag]);
            $process->run();

            if ($process->isSuccessful()) {
                $v = explode("\n", trim($process->getOutput()))[0];

                // Kontrola modulů (PDO, Tokenizer)
                $modules = $this->getPhpModules($cleanBinary);
                $features = [];
                if ($modules['pdo']) {
                    $features[] = 'PDO';
                }
                if ($modules['tokenizer']) {
                    $features[] = 'Tokenizer';
                }
                if ($modules['json']) {
                    $features[] = 'JSON';
                }

                if (! empty($features)) {
                    $v .= ' ('.implode(', ', $features).')';
                } else {
                    $v .= ' (!!! CHYBÍ PDO/TOKENIZER !!!)';
                }

                return $v;
            } else {
                $err = trim($process->getErrorOutput());
                $out = trim($process->getOutput());
                $code = $process->getExitCode();

                $msg = ($err ?: $out ?: 'Neznámá chyba');
                if ($code === 126) {
                    $msg = 'Permission denied / Not executable (Code 126). Zkuste jinou binárku.';
                } elseif ($code === 127) {
                    $msg = 'Command not found (Code 127).';
                }

                return $msg.' (Exit Code: '.$code.')';
            }
        } catch (\Throwable $e) {
            return 'Chyba při zjišťování verze: '.$e->getMessage();
        }

        return 'Neznámá verze';
    }

    protected function executeRealtime(array $cmd): void
    {
        $env = [
            'HOME' => storage_path('app'),
        ];

        // Přidání environment proměnných z BinaryHelper (např. FONTAWESOME_TOKEN)
        $extraVars = BinaryHelper::getEnvironmentVariables();
        foreach ($extraVars as $key => $val) {
            $env[$key] = $val;
        }

        // Zkusíme předat PATH z aktuálního procesu, aby byly dostupné všechny binárky (Herd, Homebrew atd.)
        $currentPath = getenv('PATH');
        if ($currentPath) {
            $env['PATH'] = $currentPath;
        }

        // POZOR: Symfony Process s polem NEPOUŽÍVÁ shell (/bin/sh)
        // To obchází problémy s právy shellu a divnými zprávami typu "Success" při selhání.
        $process = new Process($cmd, base_path(), $env);

        $cmdStr = implode(' ', array_map(function ($arg) {
            return str_contains($arg, ' ') ? escapeshellarg($arg) : $arg;
        }, $cmd));

        $this->safelyStream(content: "[RUNNING] {$cmdStr}\n\n", replace: false);

        $process->setTimeout(null);

        // Spuštění procesu a zachytávání výstupu
        $process->run(function ($type, $buffer) use ($cmd) {
            // Detekce chybějících modulů (lidsky srozumitelné)
            if (str_contains($buffer, 'Class "PDO" not found') || str_contains($buffer, 'token_get_all')) {
                $warn = "\n[!!!] CHYBA: Tato binárka PHP ({$cmd[0]}) nemá aktivní PDO nebo Tokenizer.\n";
                $warn .= "[!!!] Náprava: Zaškrtněte u příkazu 'Internal Execution' nebo nastavte funkční PHP v .env.\n";
                $this->safelyStream(content: $warn, replace: false);
            }

            // Odeslání aktualizace do frontendu přes Livewire stream (pokud je dostupný)
            $this->safelyStream(content: $buffer, replace: false);
            $this->dispatch('output-updated');
        });

        $exitCode = $process->getExitCode();
        $statusMsg = "\n[FINISHED] Exit code: $exitCode ".($exitCode === 0 ? '(SUCCESS)' : '(FAILED)')."\n";
        $this->safelyStream(content: $statusMsg, replace: false);
    }

    protected function parseCommandToArray(string $cmd): array
    {
        $parts = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';

        for ($i = 0; $i < strlen($cmd); $i++) {
            $char = $cmd[$i];
            if ($char === ' ' && ! $inQuotes) {
                if ($current !== '') {
                    $parts[] = $current;
                    $current = '';
                }

                continue;
            }
            if (($char === '"' || $char === "'") && ($i === 0 || $cmd[$i - 1] !== '\\')) {
                if ($inQuotes) {
                    if ($char === $quoteChar) {
                        $inQuotes = false;
                    } else {
                        $current .= $char;
                    }
                } else {
                    $inQuotes = true;
                    $quoteChar = $char;
                }

                continue;
            }
            $current .= $char;
        }
        if ($current !== '') {
            $parts[] = $current;
        }

        return $parts;
    }
}

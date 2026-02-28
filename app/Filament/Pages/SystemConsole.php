<?php

namespace App\Filament\Pages;

use App\Support\FilamentIcon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class SystemConsole extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-command-line';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected string $view = 'filament.pages.system-console';

    public string $output = '';

    public function mount(): void {}

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
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
        ];
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
                'icon' => FilamentIcon::get('sparkles'),
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
                    'icon' => FilamentIcon::get('rocket'),
                ],
                'app:sync' => [
                    'label' => __('admin/system-console.commands.sync.label'),
                    'desc' => __('admin/system-console.commands.sync.desc'),
                    'type' => 'artisan',
                    'color' => 'warning',
                    'icon' => FilamentIcon::get('rotate'),
                ],
                'app:local:prepare' => [
                    'label' => __('admin/system-console.commands.local_prepare.label'),
                    'desc' => __('admin/system-console.commands.local_prepare.desc'),
                    'type' => 'artisan',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('file-export'),
                ],
                'app:production:setup' => [
                    'label' => __('admin/system-console.commands.prod_setup.label'),
                    'desc' => __('admin/system-console.commands.prod_setup.desc'),
                    'type' => 'artisan',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('gears'),
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
                'icon' => FilamentIcon::get('icons'),
            ],
            'app:icons:doctor' => [
                'label' => __('admin/system-console.commands.icons_doctor.label'),
                'desc' => __('admin/system-console.commands.icons_doctor.desc'),
                'type' => 'artisan',
                'color' => 'info',
                'icon' => FilamentIcon::get('stethoscope'),
            ],
            'announcements:sync' => [
                'label' => __('admin/system-console.commands.announcements_sync.label'),
                'desc' => __('admin/system-console.commands.announcements_sync.desc'),
                'type' => 'artisan',
                'color' => 'gray',
                'icon' => FilamentIcon::get('bullhorn'),
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
                'icon' => FilamentIcon::get('money-bill-transfer'),
            ],
            'finance:cleanup' => [
                'label' => __('admin/system-console.commands.finance_cleanup.label'),
                'desc' => __('admin/system-console.commands.finance_cleanup.desc'),
                'type' => 'artisan',
                'flags' => [
                    '--force' => __('admin/system-console.commands.finance_cleanup.flags.--force'),
                ],
                'color' => 'danger',
                'icon' => FilamentIcon::get('broom'),
            ],
            'stats:import' => [
                'label' => __('admin/system-console.commands.stats_import.label'),
                'desc' => __('admin/system-console.commands.stats_import.desc'),
                'type' => 'artisan',
                'color' => 'gray',
                'icon' => FilamentIcon::get('chart-line'),
            ],
        ];

        // 4. Údržba & Čištění (Vždy)
        $groups[__('admin/system-console.groups.maintenance')] = [
            'system:cleanup' => [
                'label' => __('admin/system-console.commands.system_cleanup.label'),
                'desc' => __('admin/system-console.commands.system_cleanup.desc'),
                'type' => 'artisan',
                'color' => 'danger',
                'icon' => FilamentIcon::get('broom'),
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
                'icon' => FilamentIcon::get('clock-rotate-left'),
            ],
            'club:backfill-identifiers' => [
                'label' => __('admin/system-console.commands.backfill_ids.label'),
                'desc' => __('admin/system-console.commands.backfill_ids.desc'),
                'type' => 'artisan',
                'flags' => ['--regenerate-existing' => __('admin/system-console.commands.backfill_ids.flags.regenerate')],
                'color' => 'gray',
                'icon' => FilamentIcon::get('user-check'),
            ],
            'rsvp:reminders' => [
                'label' => __('admin/system-console.commands.rsvp_reminders.label'),
                'desc' => __('admin/system-console.commands.rsvp_reminders.desc'),
                'type' => 'artisan',
                'color' => 'info',
                'icon' => FilamentIcon::get('bell'),
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
                'icon' => FilamentIcon::get('database'),
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
                'icon' => FilamentIcon::get('undo'),
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
                'icon' => FilamentIcon::get('seedling'),
            ],
            'app:seed' => [
                'label' => __('admin/system-console.commands.app_seed.label'),
                'desc' => __('admin/system-console.commands.app_seed.desc'),
                'type' => 'artisan',
                'flags' => ['--fresh' => __('admin/system-console.commands.app_seed.flags.fresh')],
                'color' => 'gray',
                'icon' => FilamentIcon::get('seedling'),
            ],
        ];

        // 6. Optimalizace & Cache (Vždy)
        $groups[__('admin/system-console.groups.optimization')] = [
            'optimize:clear' => [
                'label' => __('admin/system-console.commands.optimize_clear.label'),
                'desc' => __('admin/system-console.commands.optimize_clear.desc'),
                'type' => 'internal',
                'color' => 'danger',
                'icon' => FilamentIcon::get('trash'),
            ],
            'config:cache' => [
                'label' => __('admin/system-console.commands.config_cache.label'),
                'desc' => __('admin/system-console.commands.config_cache.desc'),
                'type' => 'internal',
                'color' => 'primary',
                'icon' => FilamentIcon::get('gear'),
            ],
            'route:cache' => [
                'label' => __('admin/system-console.commands.route_cache.label'),
                'desc' => __('admin/system-console.commands.route_cache.desc'),
                'type' => 'internal',
                'color' => 'primary',
                'icon' => FilamentIcon::get('route'),
            ],
            'view:cache' => [
                'label' => __('admin/system-console.commands.view_cache.label'),
                'desc' => __('admin/system-console.commands.view_cache.desc'),
                'type' => 'internal',
                'color' => 'primary',
                'icon' => FilamentIcon::get('eye'),
            ],
            'storage:link' => [
                'label' => __('admin/system-console.commands.storage_link.label'),
                'desc' => __('admin/system-console.commands.storage_link.desc'),
                'type' => 'internal',
                'color' => 'info',
                'icon' => FilamentIcon::get('link'),
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
                'icon' => FilamentIcon::get('box-open'),
            ],
            'npm install' => [
                'label' => __('admin/system-console.commands.npm_install.label'),
                'desc' => __('admin/system-console.commands.npm_install.desc'),
                'type' => 'shell',
                'color' => 'gray',
                'icon' => FilamentIcon::get('download'),
            ],
            'npm run build' => [
                'label' => __('admin/system-console.commands.npm_build.label'),
                'desc' => __('admin/system-console.commands.npm_build.desc'),
                'type' => 'shell',
                'color' => 'success',
                'icon' => FilamentIcon::get('hammer'),
            ],
        ];

        if (! $isLocal) {
            $devTools['git status'] = [
                'label' => __('admin/system-console.commands.git_status.label'),
                'desc' => __('admin/system-console.commands.git_status.desc'),
                'type' => 'shell',
                'color' => 'info',
                'icon' => FilamentIcon::get('code-branch'),
            ];
            $devTools['git pull'] = [
                'label' => __('admin/system-console.commands.git_pull.label'),
                'desc' => __('admin/system-console.commands.git_pull.desc'),
                'type' => 'shell',
                'color' => 'warning',
                'icon' => FilamentIcon::get('cloud-download'),
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
                'icon' => FilamentIcon::get('stethoscope'),
            ],
            'php:basic' => [
                'label' => 'PHP: Základní info',
                'desc' => 'Verze PHP, SAPI, uživatel a webová binárka.',
                'type' => 'internal',
                'color' => 'info',
                'icon' => FilamentIcon::get('info-circle'),
            ],
            'php:ini' => [
                'label' => 'PHP: Konfigurace (INI)',
                'desc' => 'Limity a omezení PHP (disable_functions, open_basedir).',
                'type' => 'internal',
                'color' => 'info',
                'icon' => FilamentIcon::get('sliders'),
            ],
            'php -v' => [
                'label' => 'PHP CLI Version',
                'desc' => 'Zobrazí verzi PHP v systémovém shellu.',
                'type' => 'shell',
                'color' => 'gray',
                'icon' => FilamentIcon::get('php', 'fab'),
            ],
            'node -v' => [
                'label' => 'Node Version',
                'desc' => 'Zobrazí verzi Node.js na serveru.',
                'type' => 'shell',
                'color' => 'gray',
                'icon' => FilamentIcon::get('node-js', 'fab'),
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

    public function run(string $command, string $type, array $selectedFlags = [], ?string $selectName = null, ?string $selectValue = null, bool $useInternal = false): void
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
        $this->output .= "\n[$timestamp] > $command".(empty($selectedFlags) ? '' : ' '.implode(' ', $selectedFlags)).($selectValue ? " $selectValue" : '')."\n";

        try {
            if ($type === 'artisan') {
                // Zjištění cesty k PHP binárce (inteligentní finder + .env override)
                $finder = new PhpExecutableFinder;
                $phpBinary = $finder->find(false) ?: PHP_BINARY;

                if (config('app.env') === 'production') {
                    $phpBinary = config('app.prod_php_binary', $phpBinary);
                } else {
                    $phpBinary = config('app.local_php_binary', $phpBinary);
                }

                $this->streamDebugInfo($phpBinary, 'artisan');

                // Sestavení commandu jako POLE pro Symfony Process (obchází /bin/sh)
                $commandArray = [$phpBinary, 'artisan', $command, '--no-interaction'];
                foreach ($selectedFlags as $flag) {
                    $commandArray[] = $flag;
                }
                if ($selectName && $selectValue) {
                    $commandArray[] = "$selectName=$selectValue";
                }

                $this->executeRealtime($commandArray);
                $success = true;
            } else {
                $commandArray = $this->parseCommandToArray($command);

                // Mapování binárek na cesty (inteligentní finder + .env override)
                $finder = new PhpExecutableFinder;
                $defaultPhp = $finder->find(false) ?: 'php';

                $binaryMap = [
                    'php' => config('app.env') === 'production' ? (config('app.prod_php_binary') ?: $defaultPhp) : (config('app.local_php_binary') ?: $defaultPhp),
                    'node' => config('app.env') === 'production' ? (config('app.prod_node_binary') ?: 'node') : 'node',
                    'npm' => config('app.env') === 'production' ? (config('app.prod_npm_binary') ?: 'npm') : 'npm',
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
            $this->output .= "\nCHYBA: ".$e->getMessage();
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

    protected function runInternal(string $command, array $flags = [], ?string $selectName = null, ?string $selectValue = null): void
    {
        set_time_limit(0);
        @ini_set('memory_limit', '512M');
        @ignore_user_abort(true);
        $timestamp = now()->format('H:i:s');
        $this->output .= "\n[$timestamp] > (Internal) artisan $command".(empty($flags) ? '' : ' '.implode(' ', $flags)).($selectValue ? " $selectValue" : '')."\n";
        $this->stream(to: 'output', content: "\n[$timestamp] > (Internal) artisan $command".(empty($flags) ? '' : ' '.implode(' ', $flags)).($selectValue ? " $selectValue" : '')."\n", replace: false);

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
            if ($selectName && $selectValue) {
                $parameters[$selectName] = $selectValue;
            }

            // Podpora pro diagnostické interní příkazy (jako v kalkulačce)
            if ($command === 'php:basic' || $command === 'php:ini') {
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
                }

                $this->output .= $output;
                $this->stream(to: 'output', content: $output, replace: false);

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

            $this->output .= $result;
            $this->stream(to: 'output', content: $result, replace: false);

            Notification::make()
                ->title(__('admin/system-console.notifications.completed'))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $stackTrace = $e->getTraceAsString();

            $this->output .= "\nFATAL ERROR: ".$errorMessage;
            if (config('app.debug')) {
                $this->output .= "\n\nStack Trace:\n".substr($stackTrace, 0, 1000).'...';
            }

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
        $this->output .= "\n[$timestamp] > System Check (Detailed Diagnostic)\n";
        $this->stream(to: 'output', content: "\n[$timestamp] > System Check (Detailed Diagnostic)\n", replace: false);

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
        $out .= sprintf("%-25s: %s\n", 'PATH', getenv('PATH') ?: '(není v env)');
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

        // 4. HLEDÁNÍ PHP BINÁREK
        $out .= "--- [4] HLEDÁNÍ FUNKČNÍ PHP BINÁRKY ---\n";
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

        $this->output .= $out;
        $this->stream(to: 'output', content: $out, replace: false);

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

    public function clearOutput(): void
    {
        $this->output = '';
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

        $this->output .= $debug;
        $this->stream(to: 'output', content: $debug, replace: false);
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

        $this->output .= "[RUNNING] {$cmdStr}\n\n";
        $this->stream(to: 'output', content: "[RUNNING] {$cmdStr}\n\n", replace: false);

        $process->setTimeout(null);

        // Spuštění procesu a zachytávání výstupu
        $process->run(function ($type, $buffer) use ($cmd) {
            $this->output .= $buffer;

            // Detekce chybějících modulů (lidsky srozumitelné)
            if (str_contains($buffer, 'Class "PDO" not found') || str_contains($buffer, 'token_get_all')) {
                $warn = "\n[!!!] CHYBA: Tato binárka PHP ({$cmd[0]}) nemá aktivní PDO nebo Tokenizer.\n";
                $warn .= "[!!!] Náprava: Zaškrtněte u příkazu 'Internal Execution' nebo nastavte funkční PHP v .env.\n";
                $this->output .= $warn;
                $this->stream(to: 'output', content: $warn, replace: false);
            }

            // Odeslání aktualizace do frontendu přes Livewire stream (pokud je dostupný)
            $this->stream(to: 'output', content: $buffer, replace: false);
            $this->dispatch('output-updated');
        });

        $exitCode = $process->getExitCode();
        $statusMsg = "\n[FINISHED] Exit code: $exitCode ".($exitCode === 0 ? '(SUCCESS)' : '(FAILED)')."\n";
        $this->output .= $statusMsg;
        $this->stream(to: 'output', content: $statusMsg, replace: false);
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

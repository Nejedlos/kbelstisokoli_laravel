<?php

namespace App\Filament\Pages;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class SystemConsole extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static string | \UnitEnum | null $navigationGroup = null;
    protected static ?string $navigationLabel = null;
    protected static ?string $title = null;

    protected string $view = 'filament.pages.system-console';

    public string $output = '';
    public array $commandGroups = [];

    public function mount(): void
    {
        $this->commandGroups = $this->getCommandGroups();
    }

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

    protected function getCommandGroups(): array
    {
        $seeders = [];
        if (is_dir(database_path('seeders'))) {
            $files = scandir(database_path('seeders'));
            foreach ($files as $file) {
                if (str_ends_with($file, '.php')) {
                    $seeders[] = str_replace('.php', '', $file);
                }
            }
        }

        return [
            __('admin/system-console.groups.ai') => [
                'ai:index' => [
                    'label' => __('admin/system-console.commands.ai_index.label'),
                    'desc' => __('admin/system-console.commands.ai_index.desc'),
                    'type' => 'artisan',
                    'flags' => [
                        '--locale=all' => __('admin/system-console.commands.ai_index.flags.all'),
                        '--locale=cs' => __('admin/system-console.commands.ai_index.flags.cs'),
                        '--locale=en' => __('admin/system-console.commands.ai_index.flags.en'),
                        '--fresh' => __('admin/system-console.commands.ai_index.flags.fresh'),
                        '--enrich' => __('admin/system-console.commands.ai_index.flags.enrich'),
                        '--no-interaction' => __('admin/system-console.commands.ai_index.flags.no_interaction')
                    ],
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('sparkles')
                ],
            ],
            __('admin/system-console.groups.deploy') => [
                'app:deploy' => [
                    'label' => __('admin/system-console.commands.deploy.label'),
                    'desc' => __('admin/system-console.commands.deploy.desc'),
                    'type' => 'artisan',
                    'color' => 'success',
                    'icon' => FilamentIcon::get('rocket')
                ],
                'app:sync' => [
                    'label' => __('admin/system-console.commands.sync.label'),
                    'desc' => __('admin/system-console.commands.sync.desc'),
                    'type' => 'artisan',
                    'color' => 'warning',
                    'icon' => FilamentIcon::get('rotate')
                ],
                'app:local:prepare' => [
                    'label' => __('admin/system-console.commands.local_prepare.label'),
                    'desc' => __('admin/system-console.commands.local_prepare.desc'),
                    'type' => 'artisan',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('file-export')
                ],
                'app:production:setup' => [
                    'label' => __('admin/system-console.commands.prod_setup.label'),
                    'desc' => __('admin/system-console.commands.prod_setup.desc'),
                    'type' => 'artisan',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('gears')
                ],
            ],
            __('admin/system-console.groups.sync') => [
                'app:icons:sync' => [
                    'label' => __('admin/system-console.commands.icons_sync.label'),
                    'desc' => __('admin/system-console.commands.icons_sync.desc'),
                    'type' => 'artisan',
                    'flags' => ['--pro' => __('admin/system-console.commands.icons_sync.flags.pro')],
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('icons')
                ],
                'app:icons:doctor' => [
                    'label' => __('admin/system-console.commands.icons_doctor.label'),
                    'desc' => __('admin/system-console.commands.icons_doctor.desc'),
                    'type' => 'artisan',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('stethoscope')
                ],
                'announcements:sync' => [
                    'label' => __('admin/system-console.commands.announcements_sync.label'),
                    'desc' => __('admin/system-console.commands.announcements_sync.desc'),
                    'type' => 'artisan',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('bullhorn')
                ],
                'finance:sync' => [
                    'label' => __('admin/system-console.commands.finance_sync.label'),
                    'desc' => __('admin/system-console.commands.finance_sync.desc'),
                    'type' => 'artisan',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('money-bill-transfer')
                ],
                'stats:import' => [
                    'label' => __('admin/system-console.commands.stats_import.label'),
                    'desc' => __('admin/system-console.commands.stats_import.desc'),
                    'type' => 'artisan',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('chart-line')
                ],
            ],
            __('admin/system-console.groups.maintenance') => [
                'system:cleanup' => [
                    'label' => __('admin/system-console.commands.system_cleanup.label'),
                    'desc' => __('admin/system-console.commands.system_cleanup.desc'),
                    'type' => 'artisan',
                    'color' => 'danger',
                    'icon' => FilamentIcon::get('broom')
                ],
                'audit:cleanup' => [
                    'label' => __('admin/system-console.commands.audit_cleanup.label'),
                    'desc' => __('admin/system-console.commands.audit_cleanup.desc'),
                    'type' => 'artisan',
                    'flags' => [
                        '--days=30' => __('admin/system-console.commands.audit_cleanup.flags.30'),
                        '--days=90' => __('admin/system-console.commands.audit_cleanup.flags.90'),
                        '--days=180' => __('admin/system-console.commands.audit_cleanup.flags.180')
                    ],
                    'color' => 'warning',
                    'icon' => FilamentIcon::get('clock-rotate-left')
                ],
                'club:backfill-identifiers' => [
                    'label' => __('admin/system-console.commands.backfill_ids.label'),
                    'desc' => __('admin/system-console.commands.backfill_ids.desc'),
                    'type' => 'artisan',
                    'flags' => ['--regenerate-existing' => __('admin/system-console.commands.backfill_ids.flags.regenerate')],
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('user-check')
                ],
                'rsvp:reminders' => [
                    'label' => __('admin/system-console.commands.rsvp_reminders.label'),
                    'desc' => __('admin/system-console.commands.rsvp_reminders.desc'),
                    'type' => 'artisan',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('bell')
                ],
            ],
            __('admin/system-console.groups.database') => [
                'migrate' => [
                    'label' => __('admin/system-console.commands.migrate.label'),
                    'desc' => __('admin/system-console.commands.migrate.desc'),
                    'type' => 'artisan',
                    'flags' => [
                        '--force' => __('admin/system-console.commands.migrate.flags.force'),
                        '--seed' => __('admin/system-console.commands.migrate.flags.seed')
                    ],
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('database')
                ],
                'migrate:rollback' => [
                    'label' => __('admin/system-console.commands.migrate_rollback.label'),
                    'desc' => __('admin/system-console.commands.migrate_rollback.desc'),
                    'type' => 'artisan',
                    'flags' => [
                        '--force' => __('admin/system-console.commands.migrate_rollback.flags.force'),
                        '--step=1' => __('admin/system-console.commands.migrate_rollback.flags.step')
                    ],
                    'color' => 'warning',
                    'icon' => FilamentIcon::get('undo')
                ],
                'db:seed' => [
                    'label' => __('admin/system-console.commands.db_seed.label'),
                    'desc' => __('admin/system-console.commands.db_seed.desc'),
                    'type' => 'artisan',
                    'flags' => ['--force' => __('admin/system-console.commands.db_seed.flags.force')],
                    'select' => [
                        'name' => '--class',
                        'label' => __('admin/system-console.commands.db_seed.select_label'),
                        'options' => array_combine($seeders, $seeders)
                    ],
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('seedling')
                ],
                'app:seed' => [
                    'label' => __('admin/system-console.commands.app_seed.label'),
                    'desc' => __('admin/system-console.commands.app_seed.desc'),
                    'type' => 'artisan',
                    'flags' => ['--fresh' => __('admin/system-console.commands.app_seed.flags.fresh')],
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('seedling')
                ],
            ],
            __('admin/system-console.groups.optimization') => [
                'optimize:clear' => [
                    'label' => __('admin/system-console.commands.optimize_clear.label'),
                    'desc' => __('admin/system-console.commands.optimize_clear.desc'),
                    'type' => 'artisan',
                    'color' => 'danger',
                    'icon' => FilamentIcon::get('trash')
                ],
                'config:cache' => [
                    'label' => __('admin/system-console.commands.config_cache.label'),
                    'desc' => __('admin/system-console.commands.config_cache.desc'),
                    'type' => 'artisan',
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('gear')
                ],
                'route:cache' => [
                    'label' => __('admin/system-console.commands.route_cache.label'),
                    'desc' => __('admin/system-console.commands.route_cache.desc'),
                    'type' => 'artisan',
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('route')
                ],
                'view:cache' => [
                    'label' => __('admin/system-console.commands.view_cache.label'),
                    'desc' => __('admin/system-console.commands.view_cache.desc'),
                    'type' => 'artisan',
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('eye')
                ],
                'storage:link' => [
                    'label' => __('admin/system-console.commands.storage_link.label'),
                    'desc' => __('admin/system-console.commands.storage_link.desc'),
                    'type' => 'artisan',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('link')
                ],
            ],
            __('admin/system-console.groups.dev_tools') => [
                'npm install' => [
                    'label' => __('admin/system-console.commands.npm_install.label'),
                    'desc' => __('admin/system-console.commands.npm_install.desc'),
                    'type' => 'shell',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('download')
                ],
                'npm run build' => [
                    'label' => __('admin/system-console.commands.npm_build.label'),
                    'desc' => __('admin/system-console.commands.npm_build.desc'),
                    'type' => 'shell',
                    'color' => 'success',
                    'icon' => FilamentIcon::get('hammer')
                ],
                'composer install' => [
                    'label' => __('admin/system-console.commands.composer_install.label'),
                    'desc' => __('admin/system-console.commands.composer_install.desc'),
                    'type' => 'shell',
                    'flags' => [
                        '--no-dev' => __('admin/system-console.commands.composer_install.flags.no_dev'),
                        '--optimize-autoloader' => __('admin/system-console.commands.composer_install.flags.optimize')
                    ],
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('box-open')
                ],
                'git status' => [
                    'label' => __('admin/system-console.commands.git_status.label'),
                    'desc' => __('admin/system-console.commands.git_status.desc'),
                    'type' => 'shell',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('code-branch')
                ],
                'git pull' => [
                    'label' => __('admin/system-console.commands.git_pull.label'),
                    'desc' => __('admin/system-console.commands.git_pull.desc'),
                    'type' => 'shell',
                    'color' => 'warning',
                    'icon' => FilamentIcon::get('cloud-download')
                ],
            ],
            __('admin/system-console.groups.diagnostics') => [
                'php -v' => [
                    'label' => 'PHP Version',
                    'desc' => 'Zobrazí verzi PHP na serveru.',
                    'type' => 'shell',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('php', 'fab')
                ],
                'node -v' => [
                    'label' => 'Node Version',
                    'desc' => 'Zobrazí verzi Node.js na serveru.',
                    'type' => 'shell',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('node-js', 'fab')
                ],
                'npm -v' => [
                    'label' => 'NPM Version',
                    'desc' => 'Zobrazí verzi NPM na serveru.',
                    'type' => 'shell',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('npm', 'fab')
                ],
            ],
        ];
    }

    public function run(string $command, string $type, array $selectedFlags = [], ?string $selectName = null, ?string $selectValue = null): void
    {
        set_time_limit(0);
        $timestamp = now()->format('H:i:s');
        $this->output .= "\n[$timestamp] > $command" . (empty($selectedFlags) ? "" : " " . implode(' ', $selectedFlags)) . ($selectValue ? " $selectValue" : "") . "\n";

        try {
            if ($type === 'artisan') {
                // Zjištění cesty k PHP binárce (inteligentní finder + .env override)
                $finder = new PhpExecutableFinder();
                $phpBinary = $finder->find(false) ?: 'php';

                if (config('app.env') === 'production') {
                    $phpBinary = env('PROD_PHP_BINARY', $phpBinary);
                } else {
                    $phpBinary = env('LOCAL_PHP_BINARY', $phpBinary);
                }

                $phpEsc = escapeshellarg($phpBinary);
                $this->streamDebugInfo($phpBinary, 'artisan');
                $commandLine = "{$phpEsc} artisan $command";
                foreach ($selectedFlags as $flag) {
                    $commandLine .= " $flag";
                }
                if ($selectName && $selectValue) {
                    $commandLine .= " $selectName=$selectValue";
                }

                $this->executeRealtime($commandLine);
                $success = true;
            } else {
                $commandArray = $this->parseCommandToArray($command);

                // Mapování binárek na cesty (inteligentní finder + .env override)
                $finder = new PhpExecutableFinder();
                $defaultPhp = $finder->find(false) ?: 'php';

                $binaryMap = [
                    'php' => config('app.env') === 'production' ? (env('PROD_PHP_BINARY') ?: $defaultPhp) : (env('LOCAL_PHP_BINARY') ?: $defaultPhp),
                    'node' => config('app.env') === 'production' ? (env('PROD_NODE_BINARY') ?: 'node') : 'node',
                    'npm' => config('app.env') === 'production' ? (env('PROD_NPM_BINARY') ?: 'npm') : 'npm',
                    'composer' => 'composer',
                    'git' => 'git',
                ];

                $binaryPath = $commandArray[0];
                if (isset($commandArray[0]) && isset($binaryMap[$commandArray[0]])) {
                    $commandArray[0] = $binaryMap[$commandArray[0]];
                    $binaryPath = $commandArray[0];
                }

                // Pokud první binárka obsahuje mezery (např. Herd path), je nutné ji escapovat
                if (isset($commandArray[0]) && str_contains($commandArray[0], ' ')) {
                    $commandArray[0] = escapeshellarg($commandArray[0]);
                }

                $this->streamDebugInfo($binaryPath, 'shell');
                $fullCmd = implode(' ', $commandArray);
                $this->executeRealtime($fullCmd);
                $success = true;
            }

            Notification::make()
                ->title($success ? __('admin/system-console.notifications.completed') : __('admin/system-console.notifications.failed'))
                ->status($success ? 'success' : 'danger')
                ->send();

        } catch (\Exception $e) {
            $this->output .= "\nCHYBA: " . $e->getMessage();
            Log::error("SystemConsole Error: " . $e->getMessage());

            Notification::make()
                ->title(__('admin/system-console.notifications.execution_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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

        $debug = "\n[DEBUG] ------------------------------------------------------------\n";
        $debug .= "[DEBUG] Akce: " . ($type === 'artisan' ? 'Artisan Command' : 'Shell Command') . "\n";
        $debug .= "[DEBUG] Binárka: {$binaryPath}\n";
        $debug .= "[DEBUG] Verze: " . $this->getBinaryVersion($binaryPath) . "\n";
        $debug .= "[DEBUG] Adresář: {$dir}\n";
        $debug .= "[DEBUG] Uživatel: {$user}\n";
        $debug .= "[DEBUG] Prostředí: {$env}\n";
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

            $flag = '-v';
            if (str_contains($binaryLower, 'php')) {
                $flag = '-v';
            } elseif (str_contains($binaryLower, 'composer') || str_contains($binaryLower, 'git')) {
                $flag = '--version';
            } elseif (str_contains($binaryLower, 'npm') || str_contains($binaryLower, 'node')) {
                $flag = '-v';
            }

            // Escapujeme binárku pro bezpečné spuštění v ShellCommandline
            $binaryEsc = escapeshellarg($cleanBinary);
            $process = Process::fromShellCommandline($binaryEsc . ' ' . $flag);
            $process->run();

            if ($process->isSuccessful()) {
                $v = explode("\n", trim($process->getOutput()))[0];
                return $v ?: 'Verze nebyla nalezena';
            }
        } catch (\Throwable $e) {
            return 'Chyba při zjišťování verze: ' . $e->getMessage();
        }

        return 'Neznámá verze';
    }

    protected function executeRealtime(string $cmd): void
    {
        $env = [
            'HOME' => storage_path('app'),
        ];

        // Zkusíme předat PATH z aktuálního procesu, aby byly dostupné všechny binárky (Herd, Homebrew atd.)
        $currentPath = getenv('PATH');
        if ($currentPath) {
            $env['PATH'] = $currentPath;
        }

        $process = Process::fromShellCommandline($cmd, base_path(), $env);

        $process->setTimeout(null);

        // Spuštění procesu a zachytávání výstupu
        $process->run(function ($type, $buffer) {
            $this->output .= $buffer;

            // Odeslání aktualizace do frontendu přes Livewire stream (pokud je dostupný)
            // nebo prostě nechat Livewire, aby si to vzalo v dalším renderu (což ale u synchronního run() nepomůže)
            // Aby to fungovalo v reálném čase, musíme použít response()->stream() nebo podobně.
            // Nicméně v Livewire akci můžeme použít js-driven polling nebo prostě vypsat na konci.
            // ALE uživatel chce real-time. V Livewire to lze udělat přes `stream` metodu (Laravel 10.x+).
            $this->stream(to: 'output', content: $buffer, replace: false);
            $this->dispatch('output-updated');
        });
    }

    protected function parseCommandToArray(string $cmd): array
    {
        $parts = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';

        for ($i = 0; $i < strlen($cmd); $i++) {
            $char = $cmd[$i];
            if ($char === ' ' && !$inQuotes) {
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

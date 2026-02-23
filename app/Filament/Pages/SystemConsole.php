<?php

namespace App\Filament\Pages;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SystemConsole extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-command-line';
    protected static string | \UnitEnum | null $navigationGroup = 'Admin nástroje';
    protected static ?string $navigationLabel = 'Systémová konzole';
    protected static ?string $title = 'Systémová konzole';

    protected string $view = 'filament.pages.system-console';

    public string $output = '';
    public array $commandGroups = [];

    public function mount(): void
    {
        $this->commandGroups = $this->getCommandGroups();
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
            'Artisan: Databáze' => [
                'migrate' => [
                    'label' => 'Migrace (migrate)',
                    'desc' => 'Spustí chybějící databázové migrace.',
                    'type' => 'artisan',
                    'flags' => ['--force' => 'Vynutit v produkci', '--seed' => 'Spustit seedy'],
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('database')
                ],
                'migrate:rollback' => [
                    'label' => 'Vrátit migrace (rollback)',
                    'desc' => 'Vrátí zpět poslední dávku migrací.',
                    'type' => 'artisan',
                    'flags' => ['--force' => 'Vynutit', '--step=1' => 'Krok 1'],
                    'color' => 'warning',
                    'icon' => FilamentIcon::get('undo')
                ],
                'db:seed' => [
                    'label' => 'Spustit Seedy',
                    'desc' => 'Naplní databázi testovacími nebo výchozími daty.',
                    'type' => 'artisan',
                    'flags' => ['--force' => 'Vynutit'],
                    'select' => [
                        'name' => '--class',
                        'label' => 'Vybrat Seeder',
                        'options' => array_combine($seeders, $seeders)
                    ],
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('seedling')
                ],
            ],
            'Artisan: Optimalizace' => [
                'optimize:clear' => [
                    'label' => 'Optimize: Clear',
                    'desc' => 'Vymaže veškeré zakešované soubory (config, routes, views).',
                    'type' => 'artisan',
                    'color' => 'danger',
                    'icon' => FilamentIcon::get('trash')
                ],
                'config:cache' => [
                    'label' => 'Config: Cache',
                    'desc' => 'Vytvoří cache soubor pro konfiguraci (rychlejší načítání).',
                    'type' => 'artisan',
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('gear')
                ],
                'route:cache' => [
                    'label' => 'Route: Cache',
                    'desc' => 'Vytvoří cache soubor pro routy.',
                    'type' => 'artisan',
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('route')
                ],
                'view:cache' => [
                    'label' => 'View: Cache',
                    'desc' => 'Vytvoří cache soubor pro Blade šablony.',
                    'type' => 'artisan',
                    'color' => 'primary',
                    'icon' => FilamentIcon::get('eye')
                ],
                'storage:link' => [
                    'label' => 'Storage: Link',
                    'desc' => 'Vytvoří symbolický odkaz pro složku storage (nutné pro obrázky).',
                    'type' => 'artisan',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('link')
                ],
            ],
            'NPM / Vite' => [
                'npm install' => [
                    'label' => 'NPM: Install',
                    'desc' => 'Nainstaluje závislosti (node_modules).',
                    'type' => 'shell',
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('download')
                ],
                'npm run build' => [
                    'label' => 'NPM: Run Build',
                    'desc' => 'Sestaví assety (Vite) pro produkci.',
                    'type' => 'shell',
                    'color' => 'success',
                    'icon' => FilamentIcon::get('hammer')
                ],
            ],
            'Composer & Git' => [
                'composer install' => [
                    'label' => 'Composer: Install',
                    'desc' => 'Nainstaluje PHP závislosti (vendor).',
                    'type' => 'shell',
                    'flags' => ['--no-dev' => 'Bez dev balíčků', '--optimize-autoloader' => 'Optimalizovat'],
                    'color' => 'gray',
                    'icon' => FilamentIcon::get('box-open')
                ],
                'git status' => [
                    'label' => 'Git: Status',
                    'desc' => 'Zobrazí stav verzovacího systému.',
                    'type' => 'shell',
                    'color' => 'info',
                    'icon' => FilamentIcon::get('code-branch')
                ],
                'git pull' => [
                    'label' => 'Git: Pull',
                    'desc' => 'Stáhne nejnovější změny z GitHubu.',
                    'type' => 'shell',
                    'color' => 'warning',
                    'icon' => FilamentIcon::get('cloud-download')
                ],
            ],
            'Diagnostika' => [
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
        $timestamp = now()->format('H:i:s');
        $this->output .= "\n[$timestamp] > $command" . (empty($selectedFlags) ? "" : " " . implode(' ', $selectedFlags)) . ($selectValue ? " $selectValue" : "") . "\n";

        try {
            if ($type === 'artisan') {
                $args = [];
                foreach ($selectedFlags as $flag) {
                    if (str_contains($flag, '=')) {
                        [$k, $v] = explode('=', $flag, 2);
                        $args[$k] = $v;
                    } else {
                        $args[$flag] = true;
                    }
                }
                if ($selectName && $selectValue) {
                    $args[$selectName] = $selectValue;
                }

                Artisan::call($command, $args);
                $this->output .= Artisan::output();
                $success = true;
            } else {
                $commandArray = $this->parseCommandToArray($command);

                // Mapování binárek na produkční cesty, pokud jsou v .env
                $binaryMap = [
                    'php' => config('app.env') === 'production' ? (env('PROD_PHP_BINARY') ?: 'php') : 'php',
                    'node' => config('app.env') === 'production' ? (env('PROD_NODE_BINARY') ?: 'node') : 'node',
                    'npm' => config('app.env') === 'production' ? (env('PROD_NPM_BINARY') ?: 'npm') : 'npm',
                    'composer' => 'composer',
                    'git' => 'git',
                ];

                if (isset($commandArray[0]) && isset($binaryMap[$commandArray[0]])) {
                    $commandArray[0] = $binaryMap[$commandArray[0]];
                }

                // Přidání vlajek
                foreach ($selectedFlags as $flag) {
                    if (!empty($flag)) {
                        $commandArray[] = $flag;
                    }
                }
                if ($selectName && $selectValue) {
                    $commandArray[] = "$selectName=$selectValue";
                }

                $process = new Process($commandArray, base_path(), [
                    'HOME' => storage_path('app'),
                ]);

                // Na produkci může být potřeba upravit PATH pro NPM/Node
                if (config('app.env') === 'production' && ($commandArray[0] === 'npm' || str_contains($commandArray[0], 'npm'))) {
                    // Pokud známe cestu k node, přidáme ji do PATH
                    $nodeBinary = env('PROD_NODE_BINARY');
                    if ($nodeBinary && str_contains($nodeBinary, '/')) {
                        $nodePath = dirname($nodeBinary);
                        $env = $process->getEnv();
                        $env['PATH'] = $nodePath . ':' . (getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin');
                        $process->setEnv($env);
                    }
                }

                $process->setTimeout(300);
                $process->run();

                $this->output .= $process->getOutput();
                $errorOutput = $process->getErrorOutput();
                if (!empty($errorOutput)) {
                    $this->output .= "\nERROR:\n" . $errorOutput;
                }

                $success = $process->isSuccessful();
                if (!$success) {
                    $this->output .= "\nExit Code: " . $process->getExitCode();
                }
            }

            Notification::make()
                ->title($success ? 'Příkaz dokončen' : 'Příkaz selhal')
                ->status($success ? 'success' : 'danger')
                ->send();

        } catch (\Exception $e) {
            $this->output .= "\nCHYBA: " . $e->getMessage();
            Log::error("SystemConsole Error: " . $e->getMessage());

            Notification::make()
                ->title('Chyba při spouštění')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearOutput(): void
    {
        $this->output = '';
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

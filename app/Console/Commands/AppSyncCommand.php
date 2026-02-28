<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class AppSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync
                            {--force : Přepíše existující data, pokud je to podporováno dílčími příkazy}
                            {--usersync : Synchronizovat avatary, hráčské fotky a seedovat uživatele (UserSeeder)}
                            {--syncusers : Alias pro --usersync}
                            {--syncuser : Alias pro --usersync (překlep uživatele)}
                            {--ai : Vynutit reindexaci AI (standardně se v app:sync přeskakuje)}
                            {--ai-test : Testovací režim pro AI (přeskočí interakce)}
                            {--freshseed : Smaže a znovu nahraje data na produkci pomocí seederů}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provádí agregovanou synchronizaci aplikace (ikony, oznámení, finance, avatary, apod.) a volitelně synchronizaci na produkci.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('== APP SYNC START ==');

        // 1. Lokální synchronizační úlohy (ikony, finance, oznámení)
        $this->handleLocalTasks();

        // 2. Pokud jsme na produkci, zde končíme (nechceme rekurentně volat produkční sync)
        if (app()->environment('production')) {
            $this->info('== APP SYNC DONE (Production Mode) ==');
            return self::SUCCESS;
        }

        // 3. Produkční synchronizace (SSH, DB testy, FTP, Envoy)
        // Toto se spustí pouze na localhostu
        return $this->handleProductionSync();
    }

    /**
     * Spustí synchronizační úlohy pro aktuální prostředí.
     */
    protected function handleLocalTasks(): void
    {
        $this->info('--- Running Environment Data Sync ---');

        $usersync = $this->option('usersync') || $this->option('syncusers') || $this->option('syncuser');

        // Ikony
        if (class_exists(\App\Console\Commands\IconsSyncCommand::class)) {
            $this->call('app:icons:sync');
        }

        // Oznámení
        if (class_exists(\App\Console\Commands\AnnouncementsSyncCommand::class)) {
            $this->call('announcements:sync');
        }

        // Finance
        if (class_exists(\App\Console\Commands\FinanceSyncCommand::class)) {
            $this->call('finance:sync');
        }

        // Avatary (uživatelské avatary a hráčské fotky z NextAI) - pouze s --usersync
        if ($usersync && class_exists(\App\Console\Commands\AvatarsSyncCommand::class)) {
            $this->call('avatars:sync', [
                '--force' => $this->option('force'),
            ]);
        }

        // Výchozí avatary (galerie ilustrací pro výběr) - vždy při syncu, pokud existuje command
        if (class_exists(\App\Console\Commands\DefaultAvatarsSyncCommand::class)) {
            $this->info('Synchronizuji výchozí avatary...');
            // Spouštíme v dávkách po 100, abychom předešli timeoutům na produkci
            for ($offset = 0; $offset < 2000; $offset += 100) {
                $this->call('sync:default-avatars', [
                    '--force' => $this->option('force'),
                    '--limit' => 100,
                    '--offset' => $offset,
                ]);

                // Pokud už nejsou žádné další soubory, command vypíše info (my zde ale nemáme návratovou hodnotu o počtu)
                // Tak to prostě zkusíme párkrát. 2000 souborů by mělo stačit.
            }
        }
    }

    /**
     * Komplexní logika pro synchronizaci lokálu na produkci (převzato z ProductionSyncCommand).
     */
    protected function handleProductionSync(): int
    {
        $this->info('--- Starting Production Synchronization ---');

        $this->initializeEnv();

        $host = env('PROD_HOST');
        $port = env('PROD_PORT', '22');
        $user = env('PROD_USER');
        $phpBinary = env('PROD_PHP_BINARY', 'php');
        $nodeBinary = env('PROD_NODE_BINARY', 'node');
        $path = env('PROD_PATH');
        $publicPath = env('PROD_PUBLIC_PATH');

        // DB config from env
        $dbConfig = [
            'db_connection' => env('PROD_DB_CONNECTION'),
            'db_host' => env('PROD_DB_HOST'),
            'db_port' => env('PROD_DB_PORT'),
            'db_database' => env('PROD_DB_DATABASE'),
            'db_username' => env('PROD_DB_USERNAME'),
            'db_password' => env('PROD_DB_PASSWORD'),
            'db_prefix' => env('PROD_DB_PREFIX'),
        ];

        if (!$host || !$user || !$path) {
            $this->error('❌ Chybí konfigurace produkce v .env. Spusťte prosím: php artisan app:production:setup');
            return self::FAILURE;
        }

        $currentPassword = env('PROD_DB_PASSWORD');
        $dbConfig['db_password'] = $currentPassword;

        // Ověření dostupnosti PHP na serveru
        \Laravel\Prompts\info("🔍 Ověřuji dostupnost PHP na serveru...");
        $checkPhp = Process::run("ssh -p {$port} {$user}@{$host} '{$phpBinary} -v'");
        if (!$checkPhp->successful()) {
            $this->error("❌ PHP binárka '{$phpBinary}' není na serveru dostupná nebo nefunguje.");
            return self::FAILURE;
        }

        while (true) {
            // Ověření DB připojení ze serveru
            \Laravel\Prompts\info("🔍 Ověřuji DB připojení ze serveru...");

            $dbCheckPhp = '
                mysqli_report(MYSQLI_REPORT_OFF);
                $host = base64_decode("' . base64_encode($dbConfig['db_host']) . '");
                $user = base64_decode("' . base64_encode($dbConfig['db_username']) . '");
                $pass = base64_decode("' . base64_encode($dbConfig['db_password']) . '");
                $db   = base64_decode("' . base64_encode($dbConfig['db_database']) . '");
                $port = (int)base64_decode("' . base64_encode($dbConfig['db_port']) . '");

                // Pokus o připojení s ošetřením chyb
                $conn = @mysqli_init();
                if (!$conn) {
                    echo "FAIL: mysqli_init failed";
                    exit;
                }

                // Nastavení timeoutu
                mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

                $success = @mysqli_real_connect($conn, $host, $user, $pass, $db, $port);

                if ($success) {
                    echo "OK";
                    mysqli_close($conn);
                } else {
                    $error = mysqli_connect_error();
                    $errno = mysqli_connect_errno();
                    // Pokud je chyba prázdná, zkusíme vzít chybu z instance
                    if (empty($error)) {
                        $error = mysqli_error($conn);
                        $errno = mysqli_errno($conn);
                    }
                    echo "FAIL: [" . $errno . "] " . $error;
                }
            ';

            $dbCheckCmd = "ssh -p {$port} {$user}@{$host} \"{$phpBinary} -r 'eval(stream_get_contents(STDIN));'\"";
            $checkDb = Process::input($dbCheckPhp)->run($dbCheckCmd);
            $output = trim($checkDb->output());

            if ($output === 'OK') {
                \Laravel\Prompts\info("✅ DB připojení je v pořádku.");
                break;
            }

            $this->error("❌ Nelze se připojit k produkční databázi ze serveru.");
            if (!empty($output) && str_contains($output, 'FAIL:')) {
                $this->line("Důvod: " . substr($output, strpos($output, 'FAIL:') + 5));
            } elseif (!empty($checkDb->errorOutput())) {
                $this->line("Chyba: " . trim($checkDb->errorOutput()));
            }

            if ($this->option('ai-test')) {
                return self::FAILURE;
            }

            if (!confirm("Chcete zadat jiné heslo?", true)) {
                return self::FAILURE;
            }

            $dbConfig['db_password'] = password(
                label: 'Zadejte správné heslo k produkční databázi:',
                required: true
            );

            if (confirm("Chcete toto heslo uložit do lokálního .env?", true)) {
                $this->updateEnv([
                    'PROD_DB_PASSWORD' => $dbConfig['db_password'],
                    'DB_PASSWORD' => $dbConfig['db_password'],
                ]);

                $rootEnv = base_path('.env');
                if (file_exists($rootEnv)) {
                    $content = file_get_contents($rootEnv);
                    $safeValue = $dbConfig['db_password'];
                    if (str_contains($safeValue, ' ') && !str_starts_with($safeValue, '"')) {
                        $safeValue = '"' . str_replace('"', '\"', $safeValue) . '"';
                    }

                    if (preg_match("/^PROD_DB_PASSWORD=/m", $content)) {
                        $content = preg_replace("/^PROD_DB_PASSWORD=.*/m", "PROD_DB_PASSWORD={$safeValue}", $content);
                    } else {
                        $content = rtrim($content) . "\nPROD_DB_PASSWORD={$safeValue}\n";
                    }
                    file_put_contents($rootEnv, $content);
                }
            }
        }

        // Node.js selection logic
        if ($nodeBinary === 'node' || empty($nodeBinary)) {
            \Laravel\Prompts\info("🔍 Hledám optimální verzi Node.js (v18+)...");
            $findNode = Process::run("ssh -p {$port} {$user}@{$host} 'for n in $(which -a node22 node20 node18 node); do if \$n -v | grep -qE \"v(18|2[0-9])\"; then echo \$n; break; fi; done'");
            if ($findNode->successful() && !empty(trim($findNode->output()))) {
                $nodeBinary = trim($findNode->output());
                \Laravel\Prompts\info("✅ Použiji: {$nodeBinary}");
            }
        }

        // --- Nahrávání lokálních assetů ---
        \Laravel\Prompts\info("📤 Nahrávám lokální assety a build na server...");

        $usersync = $this->option('usersync') || $this->option('syncusers') || $this->option('syncuser');

        $ftpHost = env('PROD_FTP_HOST');
        $ftpUser = env('PROD_FTP_USER');
        $ftpPass = env('PROD_FTP_PASSWORD');
        $ftpPort = env('PROD_FTP_PORT', 21);

        $syncDirs = [
            'public/assets/',
            'public/build/',
            'public/uploads/defaults/',
            'database/migrations/',
            'database/seeders/',
            'database/factories/'
        ];

        // Pokud synchronizujeme uživatele, nahrajeme i jejich lokálně stažená média
        if ($usersync) {
            $syncDirs[] = 'public/uploads/media/';
            $syncDirs[] = 'public/uploads/avatars/';
        }

        foreach ($syncDirs as $dir) {
            $localDir = base_path($dir);
            if (file_exists($localDir)) {
                $this->line("Syncing $dir...");
                $synced = false;

                // Zajistíme, že cílový adresář na serveru existuje
                Process::run("ssh -p {$port} {$user}@{$host} 'mkdir -p " . escapeshellarg($path . "/" . $dir) . "'");

                // 1. Rsync
                $checkRsync = Process::run("rsync --version");
                if ($checkRsync->successful()) {
                    $rsyncCmd = "rsync -avz --delete -e 'ssh -p {$port}' " . escapeshellarg($localDir) . " {$user}@{$host}:" . escapeshellarg($path . "/" . $dir);
                    $result = Process::forever()->run($rsyncCmd, function (string $type, string $output) {
                        if ($type === 'out' && strlen(trim($output)) > 0) {
                            $this->line("  " . trim($output));
                        }
                    });
                    if ($result->successful()) {
                        $synced = true;
                    }
                }

                // 2. FTP Fallback
                if (!$synced && $ftpHost && $ftpUser) {
                    $this->line("  Trying FTP fallback for $dir...");
                    if ($this->syncViaFtp($localDir, $path . "/" . $dir, $ftpHost, $ftpUser, $ftpPass, $ftpPort)) {
                        $synced = true;
                    }
                }

                // 3. SCP Fallback
                if (!$synced) {
                    $this->line("  Falling back to SCP...");
                    $scpCmd = "scp -P {$port} -r " . escapeshellarg($localDir . ".") . " {$user}@{$host}:" . escapeshellarg($path . "/" . $dir);
                    Process::forever()->run($scpCmd);
                }
            }
        }
        \Laravel\Prompts\info("✅ Assety nahrány.");

        $npmBinary = 'npm';
        if (preg_match('/node(\d+)/', $nodeBinary, $m)) {
             $npmBinary = 'npm' . $m[1];
        }

        while (true) {
            \Laravel\Prompts\info("🚀 Synchronizuji konfiguraci na {$user}@{$host}:{$port}...");

            $params = [
                "--host=" . escapeshellarg($host),
                "--port=" . escapeshellarg($port),
                "--user=" . escapeshellarg($user),
                "--php=" . escapeshellarg($phpBinary),
                "--node=" . escapeshellarg($nodeBinary),
                "--npm=" . escapeshellarg($npmBinary),
                "--path=" . escapeshellarg($path),
            ];

            if ($this->option('freshseed')) {
                $params[] = "--freshseed=1";
            }

            if ($usersync) {
                $params[] = "--usersync=1";
            }

            if (!$this->option('ai')) {
                $params[] = "--noai=1";
            }

            if ($publicPath) {
                $params[] = "--public_path=" . escapeshellarg($publicPath);
            }

            foreach ($dbConfig as $key => $value) {
                if ($value !== null) {
                    $params[] = "--{$key}=" . escapeshellarg($value);
                }
            }

            $command = "php " . base_path('vendor/bin/envoy') . " run sync " . implode(' ', $params);

            $process = Process::forever()->run($command, function (string $type, string $output) {
                echo $output;
            });

            if ($process->successful()) {
                \Laravel\Prompts\info('🎉 Synchronizace byla úspěšně dokončena!');

                $this->line('Provedené kroky:');
                $this->line(' ✅ Aktualizace .env konfigurace na serveru');
                $this->line(' ✅ Vyčištění systémové mezipaměti');
                $this->line(' ✅ Propojení veřejné složky a oprava index.php');
                $this->line(' ✅ Synchronizace statických assetů');
                $this->line(' ✅ Spuštění idempotentních databázových migrací');
                $this->line(' ✅ Spuštění ' . ($this->option('freshseed') ? 'ČERSTVÉHO (fresh)' : 'idempotentního') . ' seedování');
                if ($usersync) {
                    $this->line(' ✅ Synchronizace uživatelů (avatary)');
                }
                $this->line(' ✅ Synchronizace ikon (Font Awesome Pro)');
                $this->line(' ✅ Optimalizace aplikace (config/route cache)');

                if ($this->option('ai')) {
                    $this->line(' ✅ Reindexace AI vyhledávání (cs/en)');
                } else {
                    $this->line(' ⏩ Reindexace AI vyhledávání přeskočena (použijte --ai pro reindexaci)');
                }

                if ($this->option('ai-test')) {
                    break;
                }

                if (!confirm('Chcete synchronizaci spustit znovu?', false)) {
                    break;
                }
            } else {
                $this->error('❌ Synchronizace selhala.');

                if ($this->option('ai-test') || !confirm('Chcete zkusit synchronizaci spustit znovu?', true)) {
                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }

    protected function syncViaFtp($localDir, $remoteDir, $host, $user, $pass, $port = 21): bool
    {
        try {
            $conn = @ftp_connect($host, $port, 10);
            if (!$conn || !@ftp_login($conn, $user, $pass)) return false;
            ftp_pasv($conn, true);
            $this->uploadRecursive($conn, $localDir, $remoteDir);
            ftp_close($conn);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function uploadRecursive($conn, $localDir, $remoteDir): void
    {
        $parts = explode('/', trim($remoteDir, '/'));
        $path = '';
        foreach ($parts as $part) {
            $path .= '/' . $part;
            if (!@ftp_chdir($conn, $path)) {
                @ftp_mkdir($conn, $path);
            }
        }
        $items = scandir($localDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $localPath = $localDir . '/' . $item;
            $remotePath = $remoteDir . '/' . $item;
            if (is_dir($localPath)) {
                $this->uploadRecursive($conn, $localPath, $remotePath);
            } else {
                @ftp_put($conn, $remotePath, $localPath, FTP_BINARY);
            }
        }
    }

    protected function updateEnv(array $data): void
    {
        $path = base_path('public/.env');
        if (!file_exists($path)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $path);
            } else {
                return;
            }
        }
        $content = file_get_contents($path);
        foreach ($data as $key => $value) {
            $safeValue = (string)$value;
            if (str_contains($safeValue, ' ') && !str_starts_with($safeValue, '"')) {
                $safeValue = '"' . str_replace('"', '\"', $safeValue) . '"';
            }
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$safeValue}", $content);
            } else {
                $content = rtrim($content) . "\n{$key}={$safeValue}\n";
            }
        }
        file_put_contents($path, $content);
    }

    protected function initializeEnv(): void
    {
        $rootEnvPath = base_path('.env');
        $publicEnvPath = base_path('public/.env');
        $exampleEnvPath = base_path('.env.example');

        if (!file_exists($publicEnvPath) && file_exists($exampleEnvPath)) {
            copy($exampleEnvPath, $publicEnvPath);
        }

        if (!file_exists($publicEnvPath)) return;

        $toTransfer = [];
        if (file_exists($exampleEnvPath)) {
            $exampleVars = \Dotenv\Dotenv::parse(file_get_contents($exampleEnvPath));
            foreach (['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PREFIX'] as $key) {
                if (isset($exampleVars[$key])) $toTransfer[$key] = $exampleVars[$key];
            }
            foreach ($exampleVars as $key => $value) {
                if (str_starts_with($key, 'PROD_')) $toTransfer[$key] = $value;
            }
        }

        if (file_exists($rootEnvPath)) {
            $rootVars = \Dotenv\Dotenv::parse(file_get_contents($rootEnvPath));
            foreach ($rootVars as $key => $value) {
                if (str_starts_with($key, 'PROD_') || in_array($key, ['APP_KEY', 'FONTAWESOME_TOKEN', 'OPENAI_API_KEY', 'ERROR_REPORT_EMAIL', 'ERROR_REPORT_SENDER'])) {
                    if (!empty($value) || $key === 'PROD_DB_PASSWORD') {
                        $toTransfer[$key] = $value;
                        if (str_starts_with($key, 'PROD_DB_')) {
                            $toTransfer[str_replace('PROD_', '', $key)] = $value;
                        }
                    }
                }
            }
        }

        if (!empty($toTransfer)) $this->updateEnv($toTransfer);

        if (file_exists($publicEnvPath)) {
            $content = file_get_contents($publicEnvPath);
            if (!preg_match('/^APP_KEY="?base64:[^" \n]+"?/m', $content)) {
                 $this->call('key:generate', ['--no-interaction' => true]);
            }
        }

        if (file_exists($publicEnvPath)) {
             try {
                 \Dotenv\Dotenv::createMutable(base_path('public'), '.env')->load();
             } catch (\Exception $e) {}
        }
    }
}

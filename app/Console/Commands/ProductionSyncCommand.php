<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class ProductionSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync {--ai-test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync configuration and run migrations on production (after FTP upload)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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
        $checkPhp = \Illuminate\Support\Facades\Process::run("ssh -p {$port} {$user}@{$host} '{$phpBinary} -v'");
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
            $checkDb = \Illuminate\Support\Facades\Process::input($dbCheckPhp)->run($dbCheckCmd);
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
                // Uložíme do public/.env (primární pro aktuální aplikaci)
                $this->updateEnv([
                    'PROD_DB_PASSWORD' => $dbConfig['db_password'],
                    'DB_PASSWORD' => $dbConfig['db_password'],
                ]);

                // Uložíme i do kořenového .env (master kopie), pokud existuje
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

        // Zajištění správné verze Node.js (Vite vyžaduje 18+)
        if ($nodeBinary === 'node' || empty($nodeBinary)) {
            \Laravel\Prompts\info("🔍 Hledám optimální verzi Node.js (v18+)...");
            $findNode = \Illuminate\Support\Facades\Process::run("ssh -p {$port} {$user}@{$host} 'for n in $(which -a node22 node20 node18 node); do if \$n -v | grep -qE \"v(18|2[0-9])\"; then echo \$n; break; fi; done'");
            if ($findNode->successful() && !empty(trim($findNode->output()))) {
                $nodeBinary = trim($findNode->output());
                \Laravel\Prompts\info("✅ Použiji: {$nodeBinary}");
            }
        }

        // --- PŘIDÁNO: Nahrávání lokálních assetů ---
        \Laravel\Prompts\info("📤 Nahrávám lokální assety a build na server...");

        $ftpHost = env('PROD_FTP_HOST');
        $ftpUser = env('PROD_FTP_USER');
        $ftpPass = env('PROD_FTP_PASSWORD');
        $ftpPort = env('PROD_FTP_PORT', 21);

        foreach (['public/assets/', 'public/build/'] as $dir) {
            $localDir = base_path($dir);
            if (file_exists($localDir)) {
                $this->line("Syncing $dir...");
                $synced = false;

                // 1. Zkusíme rsync (rychlejší a umí --delete)
                $checkRsync = \Illuminate\Support\Facades\Process::run("rsync --version");
                if ($checkRsync->successful()) {
                    $rsyncCmd = "rsync -avz --delete -e 'ssh -p {$port}' " . escapeshellarg($localDir) . " {$user}@{$host}:" . escapeshellarg($path . "/" . $dir);
                    $result = \Illuminate\Support\Facades\Process::forever()->run($rsyncCmd, function (string $type, string $output) {
                        if ($type === 'out' && strlen(trim($output)) > 0) {
                            $this->line("  " . trim($output));
                        }
                    });
                    if ($result->successful()) {
                        $synced = true;
                    }
                }

                // 2. Fallback na FTP pokud je nastaveno
                if (!$synced && $ftpHost && $ftpUser) {
                    $this->line("  Trying FTP fallback for $dir...");
                    if ($this->syncViaFtp($localDir, $path . "/" . $dir, $ftpHost, $ftpUser, $ftpPass, $ftpPort)) {
                        $synced = true;
                    }
                }

                // 3. Fallback na scp
                if (!$synced) {
                    $this->line("  Falling back to SCP...");
                    $scpCmd = "scp -P {$port} -r " . escapeshellarg($localDir . ".") . " {$user}@{$host}:" . escapeshellarg($path . "/" . $dir);
                    \Illuminate\Support\Facades\Process::forever()->run($scpCmd);
                }
            }
        }
        \Laravel\Prompts\info("✅ Assety nahrány.");
        // ------------------------------------------

        // Pokud jsme našli konkrétní node binárku, zkusíme najít i NPM
        $npmBinary = 'npm';
        if (preg_match('/node(\d+)/', $nodeBinary, $m)) {
             $npmBinary = 'npm' . $m[1];
        }

        if ($this->option('ai-test')) {
            $this->info("🚀 Synchronizuji konfiguraci (AI TEST MODE) na {$user}@{$host}:{$port}...");

            $params = [
                "--host=" . escapeshellarg($host),
                "--port=" . escapeshellarg($port),
                "--user=" . escapeshellarg($user),
                "--php=" . escapeshellarg($phpBinary),
                "--node=" . escapeshellarg($nodeBinary),
                "--npm=" . escapeshellarg($npmBinary),
                "--path=" . escapeshellarg($path),
            ];

            if ($publicPath) {
                $params[] = "--public_path=" . escapeshellarg($publicPath);
            }

            foreach ($dbConfig as $key => $value) {
                if ($value !== null) {
                    $params[] = "--{$key}=" . escapeshellarg($value);
                }
            }

            $command = "php " . base_path('vendor/bin/envoy') . " run sync " . implode(' ', $params);

            $process = \Illuminate\Support\Facades\Process::forever()->run($command, function (string $type, string $output) {
                echo $output;
            });

            if ($process->successful()) {
                $this->info('🎉 Synchronizace byla úspěšně dokončena!');

                $this->line('Provedené kroky:');
                $this->line(' ✅ Aktualizace .env konfigurace na serveru');
                $this->line(' ✅ Vyčištění systémové mezipaměti');
                $this->line(' ✅ Propojení veřejné složky a oprava index.php');
                $this->line(' ✅ Synchronizace statických assetů (build, assets, img)');
                $this->line(' ✅ Spuštění databázových migrací');
                $this->line(' ✅ Spuštění idempotentního seedování (včetně 2FA)');
                $this->line(' ✅ Synchronizace ikon (Font Awesome Pro)');
                $this->line(' ✅ Optimalizace aplikace (config/route cache)');
                $this->line(' ✅ Reindexace AI vyhledávání (cs/en)');

                return self::SUCCESS;
            } else {
                $this->error('❌ Synchronizace selhala.');
                return self::FAILURE;
            }
        }

        while (true) {
            \Laravel\Prompts\info("🚀 Synchronizuji konfiguraci na {$user}@{$host}:{$port}...");
            \Laravel\Prompts\info("💡 TIP: Před nahráním na FTP vždy spusťte lokálně: php artisan app:local:prepare");
            \Laravel\Prompts\info("💡 TIP: Nezapomeňte nahrát složky public/build/ a public/assets/ do kořene projektu na FTP.");

            $params = [
                "--host=" . escapeshellarg($host),
                "--port=" . escapeshellarg($port),
                "--user=" . escapeshellarg($user),
                "--php=" . escapeshellarg($phpBinary),
                "--node=" . escapeshellarg($nodeBinary),
                "--npm=" . escapeshellarg($npmBinary),
                "--path=" . escapeshellarg($path),
            ];

            if ($publicPath) {
                $params[] = "--public_path=" . escapeshellarg($publicPath);
            }

            foreach ($dbConfig as $key => $value) {
                if ($value !== null) {
                    $params[] = "--{$key}=" . escapeshellarg($value);
                }
            }

            $command = "php " . base_path('vendor/bin/envoy') . " run sync " . implode(' ', $params);

            $process = \Illuminate\Support\Facades\Process::forever()->run($command, function (string $type, string $output) {
                echo $output;
            });

            if ($process->successful()) {
                \Laravel\Prompts\info('🎉 Synchronizace byla úspěšně dokončena!');

                $this->line('Provedené kroky:');
                $this->line(' ✅ Aktualizace .env konfigurace na serveru');
                $this->line(' ✅ Vyčištění systémové mezipaměti (config, route, view)');
                $this->line(' ✅ Propojení veřejné složky a oprava index.php');
                $this->line(' ✅ Synchronizace statických assetů (vyčištění a kopírování do ' . ($publicPath ?: 'public') . ')');
                $this->line(' ✅ Spuštění databázových migrací');
                $this->line(' ✅ Spuštění idempotentního seedování (včetně 2FA)');
                $this->line(' ✅ Synchronizace ikon (Font Awesome Pro)');
                $this->line(' ✅ Optimalizace aplikace (config/route cache)');
                $this->line(' ✅ Reindexace AI vyhledávání (cs/en)');

                if ($publicPath) {
                    $this->newLine();
                    $this->warn("⚠️  Pozor: Pokud jste mazali obrázky lokálně, synchronizace je nyní odstranila i z veřejné složky:");
                    $this->line("   Cesta: " . $publicPath . "/assets/img/home/");
                    $this->line("   Pokud je stále vidíte, zkuste v prohlížeči Hard Refresh (Ctrl+F5 / Cmd+Shift+R).");
                }

                if (!\Laravel\Prompts\confirm('Chcete synchronizaci spustit znovu? (např. po dalším nahrání souborů)', false)) {
                    break;
                }
            } else {
                $this->error('❌ Synchronizace selhala. Zkontrolujte prosím chybové hlášky výše.');

                if (!\Laravel\Prompts\confirm('Chcete zkusit synchronizaci spustit znovu se stejným nastavením?', true)) {
                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * Synchronizuje adresář přes FTP.
     */
    protected function syncViaFtp($localDir, $remoteDir, $host, $user, $pass, $port = 21): bool
    {
        try {
            $conn = @ftp_connect($host, $port, 10);
            if (!$conn) {
                $this->error("  ❌ Could not connect to FTP host: $host");
                return false;
            }

            if (!@ftp_login($conn, $user, $pass)) {
                $this->error("  ❌ FTP login failed for user: $user");
                ftp_close($conn);
                return false;
            }

            ftp_pasv($conn, true);

            $this->uploadRecursive($conn, $localDir, $remoteDir);

            ftp_close($conn);
            return true;
        } catch (\Exception $e) {
            $this->error("  ❌ FTP Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rekurzivní nahrávání na FTP.
     */
    protected function uploadRecursive($conn, $localDir, $remoteDir): void
    {
        // Zajistíme existenci vzdáleného adresáře
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
                if (!@ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
                    $this->warn("    ⚠️ Failed to upload: $item");
                }
            }
        }
    }

    /**
     * Aktualizuje soubor .env o zadané klíče a hodnoty.
     */
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
            // Očištění hodnoty pro zápis do .env
            $safeValue = (string)$value;

            // Pokud hodnota obsahuje mezery a není v uvozovkách, obalíme ji
            if (str_contains($safeValue, ' ') && !str_starts_with($safeValue, '"')) {
                $safeValue = '"' . str_replace('"', '\"', $safeValue) . '"';
            }

            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$safeValue}", $content);
            } else {
                // Přidání na konec, pokud klíč neexistuje
                $content = rtrim($content) . "\n{$key}={$safeValue}\n";
            }
        }

        file_put_contents($path, $content);
    }

    /**
     * Inicializuje public/.env soubor kombinací .env.example a kořenového .env.
     */
    protected function initializeEnv(): void
    {
        $rootEnvPath = base_path('.env');
        $publicEnvPath = base_path('public/.env');
        $exampleEnvPath = base_path('.env.example');

        // Pokud public/.env neexistuje, vytvoříme ho z .env.example
        if (!file_exists($publicEnvPath) && file_exists($exampleEnvPath)) {
            \Laravel\Prompts\info("📄 Vytvářím public/.env ze šablony .env.example...");
            copy($exampleEnvPath, $publicEnvPath);
        }

        if (!file_exists($publicEnvPath)) {
            return;
        }

        $toTransfer = [];

        // 1. Nejprve načteme výchozí produkční hodnoty z .env.example
        if (file_exists($exampleEnvPath)) {
            $exampleVars = \Dotenv\Dotenv::parse(file_get_contents($exampleEnvPath));

            // Přenášíme základní DB konfiguraci (která je v .env.example produkční)
            foreach (['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PREFIX'] as $key) {
                if (isset($exampleVars[$key])) {
                    $toTransfer[$key] = $exampleVars[$key];
                }
            }

            // Přenášíme i PROD_ proměnné z example, pokud existují
            foreach ($exampleVars as $key => $value) {
                if (str_starts_with($key, 'PROD_')) {
                    $toTransfer[$key] = $value;
                }
            }
        }

        // 2. Poté přeneseme konfiguraci z kořenového .env (uživatelská přebití)
        if (file_exists($rootEnvPath)) {
            \Laravel\Prompts\info("🔗 Přenáším konfiguraci z kořenového .env do public/.env...");

            // Načtení kořenového .env pomocí Dotenv
            $rootVars = \Dotenv\Dotenv::parse(file_get_contents($rootEnvPath));

            foreach ($rootVars as $key => $value) {
                // Přenášíme vše co začíná PROD_, APP_KEY a další důležité klíče
                if (str_starts_with($key, 'PROD_') ||
                    $key === 'APP_KEY' ||
                    $key === 'FONTAWESOME_TOKEN' ||
                    $key === 'OPENAI_API_KEY' ||
                    $key === 'ERROR_REPORT_EMAIL' ||
                    $key === 'ERROR_REPORT_SENDER') {

                    // U DB hesla chceme i prázdnou hodnotu (pokud ji uživatel nastavil)
                    if (!empty($value) || $key === 'PROD_DB_PASSWORD') {
                        $toTransfer[$key] = $value;

                        // Speciální mapování: Pokud jde o PROD_DB_*, nastavíme i odpovídající DB_* v public/.env
                        if (str_starts_with($key, 'PROD_DB_')) {
                            $dbKey = str_replace('PROD_', '', $key);
                            $toTransfer[$dbKey] = $value;
                        }
                    }
                }
            }
        }

        if (!empty($toTransfer)) {
            $this->updateEnv($toTransfer);
        }

        // Pokud stále chybí APP_KEY v public/.env, vygenerujeme ho
        if (file_exists($publicEnvPath)) {
            $content = file_get_contents($publicEnvPath);
            // Hledáme APP_KEY= s prázdnou nebo neexistující hodnotou (včetně možných uvozovek)
            if (!preg_match('/^APP_KEY="?base64:[^" \n]+"?/m', $content)) {
                 \Laravel\Prompts\info("🔑 Generuji APP_KEY...");
                 $this->call('key:generate', ['--no-interaction' => true]);
            }
        }

        // Znovu načteme public/.env do aktuálního procesu, aby env() vracel správné hodnoty
        if (file_exists($publicEnvPath)) {
             try {
                 \Dotenv\Dotenv::createMutable(base_path('public'), '.env')->load();
             } catch (\Exception $e) {
                 // Ignorovat
             }
        }
    }
}

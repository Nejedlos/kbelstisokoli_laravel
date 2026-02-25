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
                $conn = @mysqli_connect(
                    base64_decode("' . base64_encode($dbConfig['db_host']) . '"),
                    base64_decode("' . base64_encode($dbConfig['db_username']) . '"),
                    base64_decode("' . base64_encode($dbConfig['db_password']) . '"),
                    base64_decode("' . base64_encode($dbConfig['db_database']) . '"),
                    (int)base64_decode("' . base64_encode($dbConfig['db_port']) . '")
                );
                if ($conn) {
                    echo "OK";
                    mysqli_close($conn);
                } else {
                    echo "FAIL: " . mysqli_connect_error();
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
                $this->updateEnv(['PROD_DB_PASSWORD' => $dbConfig['db_password']]);

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
                $this->line(' ✅ Spuštění databázových migrací');
                $this->line(' ✅ Spuštění idempotentního seedování (včetně 2FA)');
                $this->line(' ✅ Synchronizace ikon (Font Awesome Pro)');
                $this->line(' ✅ Optimalizace aplikace (config/route cache)');
                $this->line(' ✅ Reindexace AI vyhledávání');

                return self::SUCCESS;
            } else {
                $this->error('❌ Synchronizace selhala.');
                return self::FAILURE;
            }
        }

        while (true) {
            \Laravel\Prompts\info("🚀 Synchronizuji konfiguraci na {$user}@{$host}:{$port}...");
            \Laravel\Prompts\info("💡 TIP: Před nahráním na FTP vždy spusťte lokálně: php artisan app:local:prepare");
            \Laravel\Prompts\info("💡 TIP: Nezapomeňte nahrát složku public/build/ do kořene projektu na FTP.");

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
                $this->line(' ✅ Vyčištění systémové mezipaměti');
                $this->line(' ✅ Propojení veřejné složky a oprava index.php');
                $this->line(' ✅ Spuštění databázových migrací');
                $this->line(' ✅ Spuštění idempotentního seedování (včetně 2FA)');
                $this->line(' ✅ Synchronizace ikon (Font Awesome Pro)');
                $this->line(' ✅ Optimalizace aplikace (config/route cache)');
                $this->line(' ✅ Reindexace AI vyhledávání');

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

        // Pokud v kořeni existuje .env, vytáhneme z něj PROD_ proměnné a APP_KEY
        if (file_exists($rootEnvPath)) {
            \Laravel\Prompts\info("🔗 Přenáším konfiguraci z kořenového .env do public/.env...");

            // Načtení kořenového .env pomocí Dotenv (dočasně do pole, ne do globálního $_ENV, abychom neovlivnili zbytek)
            $rootVars = \Dotenv\Dotenv::parse(file_get_contents($rootEnvPath));

            $toTransfer = [];
            foreach ($rootVars as $key => $value) {
                // Přenášíme vše co začíná PROD_, APP_KEY a další důležité klíče
                if (str_starts_with($key, 'PROD_') ||
                    $key === 'APP_KEY' ||
                    $key === 'FONTAWESOME_TOKEN' ||
                    $key === 'OPENAI_API_KEY' ||
                    $key === 'ERROR_REPORT_EMAIL' ||
                    $key === 'ERROR_REPORT_SENDER') {

                    if (!empty($value)) {
                        $toTransfer[$key] = $value;
                    }
                }
            }

            if (!empty($toTransfer)) {
                $this->updateEnv($toTransfer);
            }
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

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProductionSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync';

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

        // Ověření dostupnosti PHP na serveru
        \Laravel\Prompts\info("🔍 Ověřuji dostupnost PHP na serveru...");
        $checkPhp = \Illuminate\Support\Facades\Process::run("ssh -p {$port} {$user}@{$host} '{$phpBinary} -v'");
        if (!$checkPhp->successful()) {
            $this->error("❌ PHP binárka '{$phpBinary}' není na serveru dostupná nebo nefunguje.");
            return self::FAILURE;
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
}

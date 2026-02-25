<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\confirm;

class ProductionDeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy application to production server';

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
        $npmBinary = env('PROD_NPM_BINARY', 'npm');
        $path = env('PROD_PATH');
        $token = env('PROD_GIT_TOKEN');
        $publicPath = env('PROD_PUBLIC_PATH');

        if (!$host || !$user || !$path || !$token) {
            error('❌ Chybí konfigurace produkce v .env. Spusťte prosím: php artisan app:production:setup');
            return self::FAILURE;
        }

        // Ověření dostupnosti binárek na serveru před spuštěním
        info("🔍 Ověřuji dostupnost binárek na serveru...");
        $checkPhp = Process::run("ssh -p {$port} {$user}@{$host} '{$phpBinary} -v'");
        if (!$checkPhp->successful()) {
            error("❌ PHP binárka '{$phpBinary}' není na serveru dostupná nebo nefunguje.");
            return self::FAILURE;
        }

        // Pokud je nodeBinary jen 'node', zkusíme v session najít v18+ verzi,
        // protože i když je v PATH, může tam být dřív v14 (častý problém na Webglobe).
        if ($nodeBinary === 'node') {
            info("🔍 Hledám optimální verzi Node.js (v18+)...");
            $findNode = Process::run("ssh -p {$port} {$user}@{$host} 'for n in $(which -a node20 node18 node); do if \$n -v | grep -qE \"v(18|2[0-9])\"; then echo \$n; break; fi; done'");
            if ($findNode->successful() && !empty(trim($findNode->output()))) {
                $nodeBinary = trim($findNode->output());
                info("✅ Použiji: {$nodeBinary}");
            }
        }

        $checkNode = Process::run("ssh -p {$port} {$user}@{$host} '{$nodeBinary} -v'");
        if (!$checkNode->successful()) {
            error("❌ Node.js binárka '{$nodeBinary}' není na serveru dostupná.");
            return self::FAILURE;
        }

        while (true) {
            info("🚀 Nasazuji na {$user}@{$host}:{$port}...");

            $params = [
                "--host=" . escapeshellarg($host),
                "--port=" . escapeshellarg($port),
                "--user=" . escapeshellarg($user),
                "--php=" . escapeshellarg($phpBinary),
                "--node=" . escapeshellarg($nodeBinary),
                "--npm=" . escapeshellarg($npmBinary),
                "--path=" . escapeshellarg($path),
                "--token=" . escapeshellarg($token),
            ];

            if ($publicPath) {
                $params[] = "--public_path=" . escapeshellarg($publicPath);
            }

            $command = base_path('vendor/bin/envoy') . " run deploy " . implode(' ', $params);

            $process = Process::forever()->run($command, function (string $type, string $output) {
                echo $output;
            });

            if ($process->successful()) {
                info('🎉 Nasazení bylo úspěšně dokončeno!');
                break;
            } else {
                error('❌ Nasazení selhalo. Zkontrolujte prosím chybové hlášky výše.');

                if (!confirm('Chcete zkusit nasazení spustit znovu se stejným nastavením?', true)) {
                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }
}

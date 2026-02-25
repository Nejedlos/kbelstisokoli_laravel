<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class ProductionDeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deploy {--ai-test}';

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

        if (!$host || !$user || !$path) {
            error('❌ Chybí konfigurace produkce v .env. Spusťte prosím: php artisan app:production:setup');
            return self::FAILURE;
        }

        $currentToken = env('PROD_GIT_TOKEN');
        $token = $currentToken;

        if (!$this->option('ai-test')) {
            if ($currentToken) {
                $choice = select(
                    label: 'Jak chcete naložit s GitHub Personal Access Tokenem?',
                    options: [
                        'keep' => 'Použít uložený token (' . substr($currentToken, 0, 4) . '...' . substr($currentToken, -4) . ')',
                        'new' => 'Zadat nové token',
                    ],
                    default: 'keep'
                );

                if ($choice === 'new') {
                    $token = password(
                        label: 'Zadejte nový GitHub Personal Access Token:',
                        placeholder: 'ghp_...',
                        required: true
                    );
                }
            } else {
                $token = password(
                    label: 'Zadejte GitHub Personal Access Token:',
                    placeholder: 'ghp_...',
                    required: true
                );
            }

            if ($token !== $currentToken) {
                if (confirm("Chcete tento token uložit do lokálního .env?", true)) {
                    $this->updateEnv(['PROD_GIT_TOKEN' => $token]);
                }
            }
        }

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

        $currentPassword = env('PROD_DB_PASSWORD');
        $dbConfig['db_password'] = $currentPassword;

        if (!$this->option('ai-test')) {
            if ($currentPassword) {
                $choice = select(
                    label: 'Jak chcete naložit s heslem k produkční databázi?',
                    options: [
                        'keep' => 'Použít uložené heslo (' . str_repeat('*', 8) . ')',
                        'new' => 'Zadat nové heslo',
                    ],
                    default: 'keep'
                );

                if ($choice === 'new') {
                    $dbConfig['db_password'] = password(
                        label: 'Zadejte nové heslo k produkční databázi:',
                        required: true
                    );
                }
            } else {
                $dbConfig['db_password'] = password(
                    label: 'Zadejte heslo k produkční databázi:',
                    required: true
                );
            }

            if ($dbConfig['db_password'] !== $currentPassword) {
                if (confirm("Chcete nové heslo uložit do lokálního .env?", true)) {
                    $this->updateEnv(['PROD_DB_PASSWORD' => $dbConfig['db_password']]);
                }
            }
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

            foreach ($dbConfig as $key => $value) {
                if ($value !== null) {
                    $params[] = "--{$key}=" . escapeshellarg($value);
                }
            }

            $command = base_path('vendor/bin/envoy') . " run deploy " . implode(' ', $params);

            $process = Process::forever()->run($command, function (string $type, string $output) {
                echo $output;
            });

            if ($process->successful()) {
                info('🎉 Nasazení bylo úspěšně dokončeno!');

                $this->line('Provedené kroky:');
                $this->line(' ✅ Aktualizace zdrojového kódu (Git fetch & reset)');
                $this->line(' ✅ Vyčištění systémové mezipaměti');
                $this->line(' ✅ Instalace PHP závislostí (Composer)');
                $this->line(' ✅ Spuštění databázových migrací');
                $this->line(' ✅ Spuštění idempotentního seedování (včetně 2FA)');
                $this->line(' ✅ Aktualizace .env konfigurace');
                $this->line(' ✅ Propojení veřejné složky a oprava index.php');
                $this->line(' ✅ Instalace a sestavení assetů (NPM & Vite)');
                $this->line(' ✅ Synchronizace ikon (Font Awesome Pro)');
                $this->line(' ✅ Optimalizace aplikace (config/route cache)');
                $this->line(' ✅ Reindexace AI vyhledávání');

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

    /**
     * Aktualizuje soubor .env o zadané klíče a hodnoty.
     */
    protected function updateEnv(array $data): void
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            if (str_contains($content, "{$key}=")) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}=\"{$value}\"", $content);
            } else {
                $content .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($path, $content);
    }
}

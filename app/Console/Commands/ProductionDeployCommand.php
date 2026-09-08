<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;

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
        $prodEnv = $this->loadProductionEnv();

        $host = $prodEnv['PROD_HOST'] ?? config('app.prod_host', env('PROD_HOST'));
        $port = $prodEnv['PROD_PORT'] ?? config('app.prod_port', env('PROD_PORT', '22'));
        $user = $prodEnv['PROD_USER'] ?? config('app.prod_user', env('PROD_USER'));
        $phpBinary = $prodEnv['PROD_PHP_BINARY'] ?? config('app.prod_php_binary', env('PROD_PHP_BINARY', 'php'));
        $nodeBinary = $prodEnv['PROD_NODE_BINARY'] ?? config('app.prod_node_binary', env('PROD_NODE_BINARY', 'node'));
        $npmBinary = $prodEnv['PROD_NPM_BINARY'] ?? config('app.prod_npm_binary', env('PROD_NPM_BINARY', 'npm'));
        $path = $prodEnv['PROD_PATH'] ?? config('app.prod_path', env('PROD_PATH'));
        $token = $prodEnv['PROD_GIT_TOKEN'] ?? config('app.prod_git_token', env('PROD_GIT_TOKEN'));
        $publicPath = $prodEnv['PROD_PUBLIC_PATH'] ?? config('app.prod_public_path', env('PROD_PUBLIC_PATH'));

        if (! $host || ! $user || ! $path) {
            error('❌ Chybí konfigurace produkce v .env. Spusťte prosím: php artisan app:production:setup');

            return self::FAILURE;
        }

        $currentToken = $prodEnv['PROD_GIT_TOKEN'] ?? env('PROD_GIT_TOKEN');
        $token = $currentToken;

        if (! $this->option('ai-test')) {
            if ($currentToken) {
                $choice = select(
                    label: 'Jak chcete naložit s GitHub Personal Access Tokenem?',
                    options: [
                        'keep' => 'Použít uložený token ('.substr((string) $currentToken, 0, 4).'...'.substr((string) $currentToken, -4).')',
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
                if (confirm('Chcete tento token uložit do lokálního .env?', true)) {
                    $this->updateEnv(['PROD_GIT_TOKEN' => $token]);
                }
            }
        }

        // Ověření dostupnosti binárek na serveru před spuštěním
        info('🔍 Ověřuji dostupnost binárek na serveru...');
        $checkPhp = Process::run("ssh -p {$port} {$user}@{$host} '{$phpBinary} -v'");
        if (! $checkPhp->successful()) {
            error("❌ PHP binárka '{$phpBinary}' není na serveru dostupná nebo nefunguje.");

            return self::FAILURE;
        }

        // Pokud je nodeBinary jen 'node', zkusíme v session najít verzi 20+,
        // protože i když je v PATH, může tam být dřív v14 (častý problém na Webglobe).
        if ($nodeBinary === 'node') {
            info('🔍 Hledám optimální verzi Node.js (v20+)...');
            $findNode = Process::run("ssh -p {$port} {$user}@{$host} 'if [ -s \"\$HOME/.nvm/nvm.sh\" ]; then . \"\$HOME/.nvm/nvm.sh\"; fi; for n in \$(which -a node22 node20 node); do if \$n -e \"process.exit(Number(process.versions.node.split(\\\".\\\")[0]) >= 20 ? 0 : 1)\"; then echo \$n; break; fi; done'");
            if ($findNode->successful() && ! empty(trim($findNode->output()))) {
                $nodeBinary = trim($findNode->output());
                info("✅ Použiji Node: {$nodeBinary}");

                // Zkusíme najít odpovídající npm (např. node20 -> npm20)
                if ($npmBinary === 'npm') {
                    $npmPart = '';
                    if (preg_match('/node(\d+)/', $nodeBinary, $m)) {
                        $npmPart = $m[1];
                    }

                    $findNpm = Process::run("ssh -p {$port} {$user}@{$host} 'for n in $(which -a npm{$npmPart} npm); do if \$n -v >/dev/null 2>&1; then echo \$n; break; fi; done'");
                    if ($findNpm->successful() && ! empty(trim($findNpm->output()))) {
                        $npmBinary = trim($findNpm->output());
                        info("✅ Použiji NPM: {$npmBinary}");
                    }
                }
            }
        }

        $checkNode = Process::run("ssh -p {$port} {$user}@{$host} '{$nodeBinary} -v'");
        if (! $checkNode->successful()) {
            error("❌ Node.js binárka '{$nodeBinary}' není na serveru dostupná.");

            return self::FAILURE;
        }

        while (true) {
            info("🚀 Nasazuji na {$user}@{$host}:{$port}...");

            $params = [
                '--host='.escapeshellarg($host),
                '--port='.escapeshellarg($port),
                '--user='.escapeshellarg($user),
                '--php='.escapeshellarg($phpBinary),
                '--node='.escapeshellarg($nodeBinary),
                '--npm='.escapeshellarg($npmBinary),
                '--path='.escapeshellarg($path),
                '--token='.escapeshellarg($token),
                '--fontawesome_token='.escapeshellarg(config('app.fontawesome_token')),
            ];

            if ($publicPath) {
                $params[] = '--public_path='.escapeshellarg($publicPath);
            }

            $command = base_path('vendor/bin/envoy').' run deploy '.implode(' ', $params);

            $process = Process::forever()->run($command, function (string $type, string $output) {
                echo $output;
            });

            if ($process->successful()) {
                info('🎉 Nasazení bylo úspěšně dokončeno!');

                $this->line('Provedené kroky:');
                $this->line(' ✅ Aktualizace zdrojového kódu (Git fetch & reset)');
                $this->line(' ✅ Vyčištění systémové mezipaměti');
                $this->line(' ✅ Instalace PHP závislostí (Composer)');
                $this->line(' ✅ Spuštění idempotentních databázových migrací');
                $this->line(' ✅ Instalace a sestavení assetů (NPM & Vite)');
                $this->line(' ✅ Optimalizace aplikace (config/route cache)');

                break;
            } else {
                error('❌ Nasazení selhalo. Zkontrolujte prosím chybové hlášky výše.');

                if (! confirm('Chcete zkusit nasazení spustit znovu se stejným nastavením?', true)) {
                    return self::FAILURE;
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * Načte obsah .env.production jako pole.
     */
    protected function loadProductionEnv(): array
    {
        $path = base_path('.env.production');

        if (! file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        $env = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Odstranění uvozovek
                if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                    $value = substr($value, 1, -1);
                } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                    $value = substr($value, 1, -1);
                }
                $env[$key] = $value;
            }
        }

        return $env;
    }

    /**
     * Aktualizuje soubor .env o zadané klíče a hodnoty.
     */
    protected function updateEnv(array $data): void
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
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

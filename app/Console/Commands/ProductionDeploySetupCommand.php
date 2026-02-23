<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\password;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class ProductionDeploySetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:production:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup production deployment (Git, Envoy, Environment)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info('🔧 Production Setup - Kbelští sokoli');

        $host = text(
            label: 'IP adresa produkčního serveru?',
            placeholder: '123.123.123.123',
            default: env('PROD_HOST', ''),
            required: true
        );

        $user = text(
            label: 'SSH uživatel na serveru?',
            placeholder: 'michal',
            default: env('PROD_USER', 'michal'),
            required: true
        );

        info("🔍 Pokouším se o skenování adresářů na serveru {$user}@{$host}...");

        $detectedPaths = $this->detectPaths($host, $user);

        if (!empty($detectedPaths)) {
            $path = select(
                label: 'Kde má být projekt umístěn? (Detekované cesty)',
                options: array_merge($detectedPaths, ['Zadat vlastní cestu...']),
                default: $detectedPaths[0]
            );

            if ($path === 'Zadat vlastní cestu...') {
                $path = text(
                    label: 'Cesta k projektu na serveru?',
                    placeholder: '/www/kbelstisokoli',
                    default: env('PROD_PATH', '/www/kbelstisokoli'),
                    required: true
                );
            }
        } else {
            warning('⚠️ Nepodařilo se automaticky detekovat cesty (nebo je server prázdný).');
            $path = text(
                label: 'Cesta k projektu na serveru?',
                placeholder: '/www/kbelstisokoli',
                default: env('PROD_PATH', '/www/kbelstisokoli'),
                required: true
            );
        }

        $token = password(
            label: 'GitHub Personal Access Token (pro Git autentikaci)?',
            placeholder: 'ghp_...',
            required: true
        );

        // Detekce a nastavení veřejného adresáře
        $publicPath = null;
        if (confirm('Chcete nastavit specifickou cestu pro veřejný adresář (např. www, public_html)?', false)) {
            $publicPath = text(
                label: 'Absolutní cesta k veřejnému adresáři na serveru?',
                placeholder: '/home/michal/www',
                required: true
            );
        }

        // Konfigurace databáze
        $dbConfig = [];
        if (confirm('Chcete nyní nakonfigurovat přístup k databázi pro produkci?', true)) {
            $dbConfig['db_host'] = text('DB Host', default: '127.0.0.1');
            $dbConfig['db_database'] = text('DB Name', required: true);
            $dbConfig['db_username'] = text('DB User', required: true);
            $dbConfig['db_password'] = password('DB Password', required: true);
        }

        // Uložit do .env pro příště
        $envData = [
            'PROD_HOST' => $host,
            'PROD_USER' => $user,
            'PROD_PATH' => $path,
            'PROD_GIT_TOKEN' => $token,
        ];

        if ($publicPath) {
            $envData['PROD_PUBLIC_PATH'] = $publicPath;
        }

        $this->updateEnv($envData);

        info('✅ Nastavení bylo uloženo do .env.');

        if (confirm('Chcete nyní spustit úvodní setup (git clone, composer, npm, atd.) na serveru?', true)) {
            $this->runEnvoySetup($host, $user, $path, $token, $publicPath, $dbConfig);
        }

        return self::SUCCESS;
    }

    protected function detectPaths(string $host, string $user): array
    {
        return spin(function () use ($host, $user) {
            // Zkusíme najít adresáře v domovské složce, které vypadají jako webové kořeny
            $process = Process::run("ssh -o ConnectTimeout=5 {$user}@{$host} 'ls -F | grep / | head -n 10'");

            if (!$process->successful()) {
                return [];
            }

            $output = $process->output();
            $lines = array_filter(explode("\n", $output));

            // Vyčistit lomítka na konci
            $paths = array_map(fn($p) => trim($p, '/'), $lines);

            // Seřadit tak, aby běžné názvy byly nahoře
            usort($paths, function($a, $b) {
                $common = ['www', 'public_html', 'web', 'domains'];
                $aScore = in_array(strtolower($a), $common) ? 1 : 0;
                $bScore = in_array(strtolower($b), $common) ? 1 : 0;
                return $bScore <=> $aScore;
            });

            return $paths;
        }, 'Skenuji server...');
    }

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

    protected function runEnvoySetup(string $host, string $user, string $path, string $token, ?string $publicPath, array $dbConfig): void
    {
        info("🚀 Spouštím Envoy setup na {$user}@{$host}...");

        $params = [
            "--host={$host}",
            "--user={$user}",
            "--path={$path}",
            "--token={$token}",
        ];

        if ($publicPath) {
            $params[] = "--public_path={$publicPath}";
        }

        foreach ($dbConfig as $key => $value) {
            $params[] = "--{$key}={$value}";
        }

        $command = "envoy run setup " . implode(' ', $params);

        $process = Process::forever()->run($command, function (string $type, string $output) {
            echo $output;
        });

        if ($process->successful()) {
            info('🎉 Setup byl úspěšně dokončen!');
        } else {
            error('❌ Setup selhal. Zkontrolujte prosím SSH přístup a chybové hlášky výše.');
        }
    }
}

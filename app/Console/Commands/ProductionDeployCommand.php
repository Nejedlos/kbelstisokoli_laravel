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
        $user = env('PROD_USER');
        $path = env('PROD_PATH');
        $token = env('PROD_GIT_TOKEN');
        $publicPath = env('PROD_PUBLIC_PATH');

        if (!$host || !$user || !$path || !$token) {
            error('❌ Chybí konfigurace produkce v .env. Spusťte prosím: php artisan app:production:setup');
            return self::FAILURE;
        }

        info("🚀 Nasazuji na {$user}@{$host}...");

        $params = [
            "--host={$host}",
            "--user={$user}",
            "--path={$path}",
            "--token={$token}",
        ];

        if ($publicPath) {
            $params[] = "--public_path={$publicPath}";
        }

        $command = "envoy run deploy " . implode(' ', $params);

        $process = Process::forever()->run($command, function (string $type, string $output) {
            echo $output;
        });

        if ($process->successful()) {
            info('🎉 Nasazení bylo úspěšně dokončeno!');
        } else {
            error('❌ Nasazení selhalo. Zkontrolujte prosím chybové hlášky výše.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

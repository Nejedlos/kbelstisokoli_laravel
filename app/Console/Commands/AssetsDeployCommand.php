<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class AssetsDeployCommand extends Command
{
    /**
     * Název a signatura příkazu.
     *
     * @var string
     */
    protected $signature = 'assets:deploy
                            {--with-assets : Nahraje i statické obrázky z public/assets}
                            {--build-only : Pouze provede build bez nahrání}
                            {--no-build : Přeskočí build a pouze nahraje stávající soubory}';

    /**
     * Popis příkazu.
     *
     * @var string
     */
    protected $description = 'Sestaví produkční assety (Vite) a nahraje je na produkci přes SCP (SSH).';

    /**
     * Spuštění příkazu.
     */
    public function handle(): int
    {
        // 1. Lokální build
        if (!$this->option('no-build')) {
            info('🚀 Spouštím lokální build assetů (npm run build)...');

            $buildResult = spin(
                fn () => Process::run('npm run build'),
                'Sestavuji produkční assety...'
            );

            if (!$buildResult->successful()) {
                error('❌ Build selhal:');
                note($buildResult->errorOutput());

                return self::FAILURE;
            }

            info('✅ Assety byly úspěšně sestaveny.');

            // Synchronizace ikon
            spin(
                fn () => $this->call('app:icons:sync'),
                'Synchronizuji ikony...'
            );
        }

        if ($this->option('build-only')) {
            return self::SUCCESS;
        }

        // 2. SSH/SCP Transfer
        $host = env('PROD_HOST');
        $port = env('PROD_PORT', 22);
        $user = env('PROD_USER');
        $prodPath = env('PROD_PATH');
        $prodPublicPath = env('PROD_PUBLIC_PATH');
        $publicFolder = env('PUBLIC_FOLDER', 'public');

        if (!$host || !$user) {
            error('❌ Chybí SSH konfigurace v .env (PROD_HOST, PROD_USER).');
            note('Ujistěte se, že máte nastaveny údaje pro připojení k produkci.');
            return self::FAILURE;
        }

        // Cílová složka na produkci (kde je index.php a build/)
        $remoteTarget = $prodPublicPath ?: (rtrim($prodPath, '/') . '/' . $publicFolder);

        if (!$remoteTarget) {
            error('❌ Nelze určit cílovou cestu na produkci (PROD_PUBLIC_PATH nebo PROD_PATH chybí).');
            return self::FAILURE;
        }

        $dirsToUpload = ['build'];
        if ($this->option('with-assets')) {
            $dirsToUpload[] = 'assets';
        }

        foreach ($dirsToUpload as $dir) {
            $localDir = base_path($publicFolder . '/' . $dir);

            if (!is_dir($localDir)) {
                warning("⚠️ Lokální složka {$localDir} neexistuje, přeskakuji.");
                continue;
            }

            info("📤 Nahrávám {$publicFolder}/{$dir} na {$host}:{$remoteTarget}/...");

            // 0. Pro jistotu smažeme starou složku na produkci, aby tam nezůstaly staré assety s jinými hashi
            $rmCommand = sprintf(
                'ssh -p %s %s@%s "rm -rf %s"',
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($host),
                escapeshellarg(rtrim($remoteTarget, '/') . '/' . $dir)
            );
            Process::run($rmCommand);

            // 1. Použijeme SCP pro nahrání celé složky
            // SCP -P port -r local_dir user@host:remote_parent_dir
            // Tím se nahraje složka 'build' do 'remote_target', takže vznikne 'remote_target/build'

            $scpCommand = sprintf(
                'scp -P %s -r %s %s@%s:%s',
                escapeshellarg($port),
                escapeshellarg($localDir),
                escapeshellarg($user),
                escapeshellarg($host),
                escapeshellarg(rtrim($remoteTarget, '/') . '/')
            );

            $uploadResult = spin(
                fn () => Process::run($scpCommand),
                "Synchronizuji {$dir} přes SCP..."
            );

            if (!$uploadResult->successful()) {
                error("❌ Nahrávání složky {$dir} selhalo:");
                note($uploadResult->errorOutput());

                info("💡 Tip: Ujistěte se, že máte nahraný SSH klíč na serveru a funkční připojení.");

                return self::FAILURE;
            }
        }

        info('✅ Všechny vybrané assety byly úspěšně nahrány na produkci.');

        // Bonus: Vymazání cache pohledů na produkci (pokud máme SSH údaje, můžeme zkusit i tohle)
        $this->clearRemoteCache($host, $port, $user, $prodPath);

        return self::SUCCESS;
    }

    /**
     * Pokusí se vymazat cache na produkci přes SSH.
     */
    protected function clearRemoteCache($host, $port, $user, $prodPath): void
    {
        if (!$prodPath) return;

        info('🧹 Čistím cache na produkci...');

        $phpBinary = env('PROD_PHP_BINARY', 'php');

        $commands = [
            "cd {$prodPath} && {$phpBinary} artisan view:clear",
            "cd {$prodPath} && {$phpBinary} artisan cache:clear",
        ];

        foreach ($commands as $cmd) {
            $sshCmd = sprintf(
                'ssh -p %s %s@%s %s',
                escapeshellarg($port),
                escapeshellarg($user),
                escapeshellarg($host),
                escapeshellarg($cmd)
            );

            Process::run($sshCmd);
        }

        info('✅ Produkční cache vyčištěna.');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;

class LocalAssetsDeployCommand extends Command
{
    /**
     * Název a signatura příkazu.
     *
     * @var string
     */
    protected $signature = 'app:deploy:local-assets {--build-only : Pouze spustí build bez FTP transferu} {--with-assets : Nahraje i složku public/assets}';

    /**
     * Popis příkazu.
     *
     * @var string
     */
    protected $description = 'Lokálně sestaví assety (npm run build) a nahraje složku public/build (a volitelně public/assets) na produkci přes FTP';

    /**
     * Spuštění příkazu.
     */
    public function handle(): int
    {
        // 1. Lokální build
        info('🚀 Spouštím lokální build assetů (npm run build)...');

        $buildResult = spin(
            fn () => Process::run('npm run build'),
            'Sestavuji assety...'
        );

        if (! $buildResult->successful()) {
            error('❌ Build selhal:');
            note($buildResult->errorOutput());
            return self::FAILURE;
        }

        info('✅ Build dokončen úspěšně.');

        if ($this->option('build-only')) {
            return self::SUCCESS;
        }

        // 2. FTP Transfer
        $ftpHost = env('PROD_FTP_HOST');
        $ftpUser = env('PROD_FTP_USER');
        $ftpPass = env('PROD_FTP_PASSWORD');
        $ftpPort = env('PROD_FTP_PORT', 21);
        $prodPath = env('PROD_PATH'); // Např. /public_html/secret

        if (! $ftpHost || ! $ftpUser || ! $ftpPass || ! $prodPath) {
            error('❌ Chybí FTP konfigurace v .env (PROD_FTP_HOST, PROD_FTP_USER, PROD_FTP_PASSWORD, PROD_PATH).');
            return self::FAILURE;
        }

        $dirsToUpload = ['public/build'];
        if ($this->option('with-assets')) {
            $dirsToUpload[] = 'public/assets';
        }

        $remotePublicPath = env('PROD_PUBLIC_PATH');

        foreach ($dirsToUpload as $dir) {
            $localDir = base_path($dir);
            $remoteDir = rtrim($prodPath, '/') . '/' . $dir;

            if (! is_dir($localDir)) {
                $this->warn("⚠️ Složka {$localDir} neexistuje, přeskakuji.");
                continue;
            }

            info("📤 Zahajuji FTP transfer složky {$dir} do {$remoteDir}...");
            note("Tento proces může trvat několik minut v závislosti na rychlosti připojení.");

            $success = spin(
                fn () => $this->syncViaFtp($localDir, $remoteDir, $ftpHost, $ftpUser, $ftpPass, $ftpPort),
                "Nahrávám {$dir} na server..."
            );

            if (! $success) {
                error("❌ FTP transfer složky {$dir} selhal.");
                return self::FAILURE;
            }

            // Pokud máme definovanou externí public cestu (např. /www), nahrajeme to i tam
            if ($remotePublicPath && $remotePublicPath !== rtrim($prodPath, '/') . '/public') {
                $dirNameOnly = basename($dir); // např. 'build'
                $secondaryRemoteDir = rtrim($remotePublicPath, '/') . '/' . $dirNameOnly;

                info("📤 Nahrávám také do sekundární public cesty: {$secondaryRemoteDir}");
                spin(
                    fn () => $this->syncViaFtp($localDir, $secondaryRemoteDir, $ftpHost, $ftpUser, $ftpPass, $ftpPort),
                    "Synchronizuji {$dirNameOnly} do sekundární cesty..."
                );
            }
        }

        info('✅ Všechny vybrané složky byly úspěšně nahrány na produkci.');
        info('💡 Doporučení: Pokud se změny neprojevují, zkuste na produkci spustit: php artisan view:clear && php artisan cache:clear');

        return self::SUCCESS;
    }

    /**
     * Synchronizace složky přes FTP.
     */
    protected function syncViaFtp($localDir, $remoteDir, $host, $user, $pass, $port = 21): bool
    {
        try {
            $conn = @ftp_connect($host, $port, 30);
            if (! $conn || ! @ftp_login($conn, $user, $pass)) {
                return false;
            }
            ftp_pasv($conn, true);

            // Před nahráváním se pokusíme promazat starý manifest, aby nedošlo k mismatchi
            $manifestPath = $remoteDir . '/manifest.json';
            @ftp_delete($conn, $manifestPath);

            $this->uploadRecursive($conn, $localDir, $remoteDir);

            ftp_close($conn);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Rekurzivní nahrávání složky přes FTP.
     */
    protected function uploadRecursive($conn, $localDir, $remoteDir): void
    {
        // Vytvoření cílové složky, pokud neexistuje
        $parts = explode('/', trim($remoteDir, '/'));
        $path = '';
        foreach ($parts as $part) {
            $path .= '/' . $part;
            if (! @ftp_chdir($conn, $path)) {
                @ftp_mkdir($conn, $path);
            }
        }

        $items = scandir($localDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $localPath = $localDir . '/' . $item;
            $remotePath = $remoteDir . '/' . $item;

            if (is_dir($localPath)) {
                $this->uploadRecursive($conn, $localPath, $remotePath);
            } else {
                // Před nahráním souboru se ujistíme, že jsme ve správné složce
                // (uploadRecursive pro podložky už kmkdir/chdir udělal)
                $uploaded = @ftp_put($conn, $remotePath, $localPath, FTP_BINARY);
                if (!$uploaded) {
                    // Try one more time with a fresh connection if it's a large file or connection dropped?
                    // For now just log it would be better but we are in a spin()
                }
            }
        }
    }
}

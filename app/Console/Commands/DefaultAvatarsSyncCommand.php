<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DefaultAvatarsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:default-avatars
                            {--force : Přepíše existující avatary, pokud již existují (vynutí update)}
                            {--limit=0 : Počet souborů ke zpracování v této dávce (0 = vše)}
                            {--offset=0 : Od kterého souboru začít (pro postupné zpracování)}
                            {--stop-on-error : Zastaví zpracování při první chybě}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import výchozích avatarů ze storage do složky public/uploads/defaults pro galerii.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '2G');
        set_time_limit(0);

        $sourceDir = storage_path('app/defaults/avatars');
        $targetPath = public_path('uploads/defaults');

        if (! is_dir($sourceDir)) {
            $msg = "Zdrojový adresář neexistuje: {$sourceDir}. Ujistěte se, že jste nahráli avatary do storage/app/defaults/avatars/";
            $this->error($msg);
            \Illuminate\Support\Facades\Log::error('DefaultAvatarsSyncCommand: '.$msg);

            return Command::FAILURE;
        }

        if (! is_dir($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
        }

        $allFiles = File::allFiles($sourceDir);

        // Filtrování relevantních souborů (bez thumbs a jen obrázky)
        $filteredFiles = [];
        foreach ($allFiles as $file) {
            if (Str::contains($file->getRelativePathname(), 'thumbs/')) {
                continue;
            }
            if (! in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }
            $filteredFiles[] = $file;
        }

        $totalFound = count($filteredFiles);
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');

        if ($limit > 0 || $offset > 0) {
            $filteredFiles = array_slice($filteredFiles, $offset, $limit ?: null);
        }

        $totalToProcess = count($filteredFiles);
        $this->info("Nalezeno celkem {$totalFound} relevantních souborů.");
        $this->info("Zpracovávám dávku {$totalToProcess} souborů.");

        $bar = $this->output->createProgressBar($totalToProcess);
        $bar->start();

        // Načtení stávajících MD5 hashů pro rychlou kontrolu duplicit
        $existingHashes = [];
        if (File::exists($targetPath)) {
            $existingFiles = File::allFiles($targetPath);
            foreach ($existingFiles as $file) {
                // Kontrolujeme jen hlavní avatary, ne konverze/thumb
                if (! str_contains($file->getRelativePathname(), 'conversions') && $file->getExtension() === 'webp') {
                    $existingHashes[md5_file($file->getRealPath())] = true;
                }
            }
        }

        $countImported = 0;
        $countSkipped = 0;
        $countErrors = 0;

        foreach ($filteredFiles as $file) {
            try {
                // Pro každý soubor vytvoříme dočasný WebP pro MD5 kontrolu (stejně jako v AvatarModal)
                $tempFile = tempnam(sys_get_temp_dir(), 'avatar_sync_').'.webp';
                $this->resizeToWebp($file->getRealPath(), $tempFile, 400, 400);

                if (! file_exists($tempFile)) {
                    $countErrors++;
                    $bar->advance();

                    continue;
                }

                $newMd5 = md5_file($tempFile);

                if (isset($existingHashes[$newMd5]) && ! $this->option('force')) {
                    @unlink($tempFile);
                    $countSkipped++;
                    $bar->advance();

                    continue;
                }

                // Generujeme nové ID (složku) - najdeme nejvyšší stávající a přičteme 1
                $directories = File::directories($targetPath);
                $maxId = 0;
                foreach ($directories as $dir) {
                    $id = basename($dir);
                    if (is_numeric($id) && (int) $id > $maxId) {
                        $maxId = (int) $id;
                    }
                }
                $newId = $maxId + 1;
                $newDirPath = $targetPath.'/'.$newId;
                $conversionsPath = $newDirPath.'/conversions';

                if (! File::exists($conversionsPath)) {
                    File::makeDirectory($conversionsPath, 0755, true);
                }

                $fileName = 'avatar-'.time().'-'.Str::random(5).'.webp';
                $thumbName = str_replace('.webp', '-thumb.webp', $fileName);

                // Přesuneme dočasný soubor na finální místo
                File::move($tempFile, $newDirPath.'/'.$fileName);

                // Vytvoříme thumb
                $this->resizeToWebp($file->getRealPath(), $conversionsPath.'/'.$thumbName, 100, 100);

                $existingHashes[$newMd5] = true;
                $countImported++;
            } catch (\Exception $e) {
                $countErrors++;
                if ($this->option('stop-on-error')) {
                    $this->error("\nCHYBA u souboru ".$file->getRelativePathname().': '.$e->getMessage());
                    break;
                }
            }

            $bar->advance();

            if ($countImported % 10 === 0) {
                gc_collect_cycles();
            }
        }

        $bar->finish();
        $summary = "Synchronizace dokončena. Importováno: {$countImported}, Přeskočeno: {$countSkipped}, Chyby: {$countErrors}.";
        $this->info("\n\n".$summary);
        \Illuminate\Support\Facades\Log::info('DefaultAvatarsSyncCommand: '.$summary);

        return Command::SUCCESS;
    }

    /**
     * Zmenší obrázek a převede ho na WebP.
     */
    protected function resizeToWebp($sourcePath, $targetPath, $width, $height)
    {
        $info = @getimagesize($sourcePath);
        if (! $info) {
            return;
        }

        $mime = $info['mime'];
        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            'image/gif' => @imagecreatefromgif($sourcePath),
            default => null,
        };

        if (! $src) {
            return;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        // Square crop (center)
        $minSize = min($srcW, $srcH);
        $srcX = ($srcW - $minSize) / 2;
        $srcY = ($srcH - $minSize) / 2;

        $dst = imagecreatetruecolor($width, $height);

        // Preserve transparency
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);
        imagealphablending($dst, true);

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $width, $height, $minSize, $minSize);

        imagewebp($dst, $targetPath, 85);

        imagedestroy($src);
        imagedestroy($dst);
    }
}

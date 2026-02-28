<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DefaultAvatarsSyncCommand extends Command
{
    protected $signature = 'sync:default-avatars {--force : Přepíše existující avatary, pokud již existují (vynutí update)}';
    protected $description = 'Jednorázový manuální import výchozích avatarů z lokálního úložiště do MediaAsset galerie.';

    public function handle()
    {
        ini_set('memory_limit', '2G');
        set_time_limit(0);

        $sourceDir = storage_path('app/defaults/avatars');

        if (!is_dir($sourceDir)) {
            $this->error("Zdrojový adresář neexistuje: {$sourceDir}. Ujistěte se, že jste nahráli avatary do storage/app/defaults/avatars/");
            return Command::FAILURE;
        }

        $allFiles = File::allFiles($sourceDir);
        $totalFiles = count($allFiles);
        $this->info("Nalezeno {$totalFiles} souborů k synchronizaci.");

        $bar = $this->output->createProgressBar($totalFiles);

        $countImported = 0;
        $countSkipped = 0;
        $disk = config('media-library.disk_name', 'public_path');

        // Optimalizace: Načtení všech existujících názvů souborů do paměti najednou
        $existingMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', MediaAsset::class)
            ->pluck('file_name', 'model_id')
            ->toArray();
        $existingFileNames = array_flip($existingMedia);

        foreach ($allFiles as $file) {
            // Ignorovat náhledy (thumbs)
            if (Str::contains($file->getRelativePathname(), 'thumbs/')) {
                $bar->advance();
                continue;
            }

            if (!in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'webp'])) {
                $bar->advance();
                continue;
            }

            $fileName = $file->getFilename();

            // Rychlá kontrola v poli v paměti místo DB query
            $existingId = $existingFileNames[$fileName] ?? null;

            if ($existingId && !$this->option('force')) {
                $countSkipped++;
                $bar->advance();
                continue;
            }

            if ($existingId) {
                $asset = MediaAsset::find($existingId);
                if ($asset) {
                    $asset->clearMediaCollection('default');
                } else {
                    // Pokud existuje záznam v media, ale ne model (sirotek), vytvoříme nový
                    $asset = MediaAsset::create([
                        'title' => 'Default Avatar ' . Str::random(6),
                        'is_public' => true,
                        'access_level' => 'public',
                        'type' => 'image',
                    ]);
                }
            } else {
                $asset = MediaAsset::create([
                    'title' => 'Default Avatar ' . Str::random(6),
                    'is_public' => true,
                    'access_level' => 'public',
                    'type' => 'image',
                ]);
            }

            // Přidání média
            try {
                $asset->addMedia($file->getPathname())
                    ->preservingOriginal()
                    ->toMediaCollection('default', $disk);
            } catch (\Exception $e) {
                $this->error("\nChyba při zpracování souboru " . $file->getFilename() . ": " . $e->getMessage());
            }

            $countImported++;
            $bar->advance();

            // Uvolnění paměti po každém 10. souboru
            if ($countImported % 10 === 0) {
                gc_collect_cycles();
            }
        }

        $bar->finish();
        $this->info("\nSynchronizace dokončena. Importováno: {$countImported}, Přeskočeno: {$countSkipped}.");
        return Command::SUCCESS;
    }
}

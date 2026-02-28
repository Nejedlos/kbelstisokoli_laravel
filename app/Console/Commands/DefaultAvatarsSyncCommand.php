<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DefaultAvatarsSyncCommand extends Command
{
    protected $signature = 'sync:default-avatars
                            {--force : Přepíše existující avatary, pokud již existují (vynutí update)}
                            {--limit=0 : Počet souborů ke zpracování v této dávce (0 = vše)}
                            {--offset=0 : Od kterého souboru začít (pro postupné zpracování)}
                            {--stop-on-error : Zastaví zpracování při první chybě}';
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

        // Filtrování relevantních souborů (bez thumbs a jen obrázky)
        $filteredFiles = [];
        foreach ($allFiles as $file) {
            if (Str::contains($file->getRelativePathname(), 'thumbs/')) {
                continue;
            }
            if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }
            $filteredFiles[] = $file;
        }

        $totalFound = count($filteredFiles);
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');

        if ($offset >= $totalFound && $totalFound > 0) {
            $this->info("Všechny soubory ({$totalFound}) již byly zpracovány. Končím.");
            return Command::SUCCESS;
        }

        if ($limit > 0 || $offset > 0) {
            $filteredFiles = array_slice($filteredFiles, $offset, $limit ?: null);
        }

        $totalToProcess = count($filteredFiles);
        $this->info("Nalezeno celkem {$totalFound} relevantních souborů.");
        $this->info("Zpracovávám dávku {$totalToProcess} souborů (offset: {$offset}, limit: " . ($limit ?: 'vše') . ").");

        $bar = $this->output->createProgressBar($totalToProcess);
        $bar->start();

        $countImported = 0;
        $countSkipped = 0;
        $countErrors = 0;
        $disk = config('media-library.disk_name', 'public_path');

        // Optimalizace: Načtení všech existujících názvů souborů do paměti najednou
        $existingMedia = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', MediaAsset::class)
            ->pluck('file_name', 'model_id')
            ->toArray();
        $existingFileNames = array_flip($existingMedia);

        foreach ($filteredFiles as $index => $file) {
            $fileName = $file->getFilename();
            $currentPos = $offset + $index + 1;

            // Logování aktuálního souboru (před zpracováním)
            // Použijeme line přepisování pro přehlednost, nebo info pro debug
            $this->info("\n[{$currentPos}/{$totalFound}] Zpracovávám: " . $file->getRelativePathname());

            // Rychlá kontrola v poli v paměti místo DB query
            $existingId = $existingFileNames[$fileName] ?? null;

            if ($existingId && !$this->option('force')) {
                $countSkipped++;
                $bar->advance();
                continue;
            }

            try {
                if ($existingId) {
                    $asset = MediaAsset::find($existingId);
                    if ($asset) {
                        // Zajistit, aby existující asset byl veřejný a měl správná metadata
                        $asset->update([
                            'is_public' => true,
                            'access_level' => 'public',
                            'type' => 'image',
                        ]);
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
                $asset->addMedia($file->getPathname())
                    ->preservingOriginal()
                    ->toMediaCollection('default', $disk);

                $countImported++;
            } catch (\Exception $e) {
                $countErrors++;
                $this->error("\nCHYBA u souboru " . $file->getRelativePathname() . ": " . $e->getMessage());

                if ($this->option('stop-on-error')) {
                    $this->error("Zastavuji zpracování kvůli chybě (--stop-on-error).");
                    break;
                }
            }

            $bar->advance();

            // Uvolnění paměti po každém 10. souboru
            if ($countImported % 10 === 0) {
                gc_collect_cycles();
            }
        }

        $bar->finish();
        $this->info("\n\nSynchronizace dávky dokončena.");
        $this->info("Importováno: {$countImported}");
        $this->info("Přeskočeno: {$countSkipped}");
        $this->info("Chyby: {$countErrors}");

        if ($totalToProcess + $offset < $totalFound) {
            $nextOffset = $offset + $totalToProcess;
            $this->warn("Zbývá ještě " . ($totalFound - $nextOffset) . " souborů. Spusťte znovu s --offset={$nextOffset}");
        }

        return Command::SUCCESS;
    }
}

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
        $sourceDir = storage_path('app/defaults/avatars');

        if (!is_dir($sourceDir)) {
            $this->error("Zdrojový adresář neexistuje: {$sourceDir}. Ujistěte se, že jste nahráli avatary do storage/app/defaults/avatars/");
            return Command::FAILURE;
        }

        $allFiles = File::allFiles($sourceDir);
        $bar = $this->output->createProgressBar(count($allFiles));

        $countImported = 0;
        $countSkipped = 0;
        $disk = config('media-library.disk_name', 'public_path');

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

            // Kontrola, zda už neexistuje (podle názvu souboru v media tabulce)
            $existing = MediaAsset::whereHas('media', function($query) use ($fileName) {
                    $query->where('file_name', $fileName);
                })->first();

            if ($existing && !$this->option('force')) {
                $countSkipped++;
                $bar->advance();
                continue;
            }

            if ($existing && $this->option('force')) {
                $existing->clearMediaCollection('default');
                $asset = $existing;
            } else {
                $asset = MediaAsset::create([
                    'title' => 'Default Avatar ' . Str::random(6),
                    'is_public' => true,
                    'access_level' => 'public',
                    'type' => 'image',
                ]);
            }

            $asset->addMedia($file->getPathname())
                ->preservingOriginal()
                ->toMediaCollection('default', $disk);

            $countImported++;
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nSynchronizace dokončena. Importováno: {$countImported}, Přeskočeno: {$countSkipped}.");
        return Command::SUCCESS;
    }
}

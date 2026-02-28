<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportDefaultAvatars extends Command
{
    protected $signature = 'import:default-avatars';
    protected $description = 'Importuje výchozí avatary z NextAI do MediaAsset galerie.';

    public function handle()
    {
        $sourceDir = '/Users/michalnejedly/dev/www-stack/www/nextai.localhost/public/uploads/defaults/avatars/avatar_library/2025/11';

        if (!is_dir($sourceDir)) {
            $this->error("Zdrojový adresář neexistuje: {$sourceDir}");
            return;
        }

        $files = File::files($sourceDir);
        $bar = $this->output->createProgressBar(count($files));

        foreach ($files as $file) {
            if (!in_array($file->getExtension(), ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            $title = 'Default Avatar ' . Str::random(6);

            // Kontrola, zda už neexistuje (podle názvu souboru)
            $existing = MediaAsset::where('title', 'LIKE', 'Default Avatar%')
                ->whereHas('media', function($query) use ($file) {
                    $query->where('file_name', $file->getFilename());
                })->first();

            if ($existing) {
                $bar->advance();
                continue;
            }

            $asset = MediaAsset::create([
                'title' => $title,
                'is_public' => true,
                'access_level' => 'public',
                'type' => 'image',
            ]);

            $asset->addMedia($file->getPathname())
                ->preservingOriginal()
                ->toMediaCollection('default');

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nImport dokončen.");
    }
}

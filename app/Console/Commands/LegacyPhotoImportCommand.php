<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use App\Models\PhotoPool;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LegacyPhotoImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:legacy:import-photos
                            {--limit= : Maximální počet galerií k importu}
                            {--pool-id= : Importovat pouze konkrétní galerii (podle ID ze staré DB)}
                            {--dry-run : Pouze simulace bez změn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importuje fotografie ze starého webu (složka fotoalbum) do nových Photo Poolů.';

    /**
     * Cesta k fotoalbu na produkci.
     */
    protected string $oldFotoPath = '/home/html/kbelstisokoli.cz/public_html/www/fotoalbum';

    /**
     * Cache pro existující pooly.
     */
    protected $poolsCache = null;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Přednačteme pooly (protože MySQL 5.5 nepodporuje JSON unquote v Eloquentu)
        $this->poolsCache = PhotoPool::all();

        if (!File::exists($this->oldFotoPath) && !app()->environment('local')) {
            $this->error("Složka s fotografiemi neexistuje: {$this->oldFotoPath}");
            return self::FAILURE;
        }

        // Lokální vývoj - cesta může být jiná
        if (app()->environment('local')) {
            $this->oldFotoPath = base_path('../kbelstisokoli_old/fotoalbum');
            if (!File::exists($this->oldFotoPath)) {
                $this->error("Lokální složka neexistuje: {$this->oldFotoPath}");
                return self::FAILURE;
            }
        }

        $this->info('Zahajuji import fotografií z legacy systému...');

        // 1. Získání starých galerií
        $query = DB::connection('old_mysql')->table('foto_galerie');
        if ($this->option('pool-id')) {
            $query->where('id', $this->option('pool-id'));
        }
        if ($this->option('limit')) {
            $query->limit((int)$this->option('limit'));
        }

        $oldGalleries = $query->get();

        foreach ($oldGalleries as $oldGallery) {
            $this->importGallery($oldGallery);
            gc_collect_cycles();
        }

        // 2. Import fotografií v rootu
        $this->importRootPhotos();

        $this->info('Import byl dokončen.');

        return self::SUCCESS;
    }

    /**
     * Importuje konkrétní galerii.
     */
    protected function importGallery($oldGallery): void
    {
        $title = $this->decode($oldGallery->nadpis);
        $this->info("Zpracovávám galerii: {$title} (ID: {$oldGallery->id})");

        // Najdeme složku na disku - zkusíme ji zjistit z prvního záznamu v foto_fotky
        $photoEntry = DB::connection('old_mysql')->table('foto_fotky')
            ->where('galerie', $oldGallery->id)
            ->first();

        if (!$photoEntry || !$photoEntry->slozka) {
            $this->warn("Žádné fotky pro galerii ID: {$oldGallery->id} v databázi");
            return;
        }

        $folderName = $photoEntry->slozka;
        $folderPath = $this->oldFotoPath . '/' . $folderName;

        if (!File::isDirectory($folderPath)) {
            $this->warn("Složka {$folderName} nalezena v DB, ale na disku chybí: {$folderPath}");
            return;
        }

        $this->info("Nalezena složka: {$folderName}");

        // Najdeme nebo vytvoříme PhotoPool
        $pool = $this->findOrCreatePool($oldGallery, $title);

        if (!$pool) {
            $this->error("Nepodařilo se najít ani vytvořit PhotoPool pro: {$title}");
            return;
        }

        // Importujeme fotky ze složky
        $files = File::files($folderPath);
        $this->processPhotos($pool, $files, $oldGallery->id);
    }

    /**
     * Najde existující pool nebo vytvoří nový.
     */
    protected function findOrCreatePool($oldGallery, string $title): ?PhotoPool
    {
        $slug = Str::slug($title);

        // Zkusíme najít v cache
        $pool = $this->poolsCache->filter(function($p) use ($slug, $title) {
            return $p->slug === $slug || $p->getTranslation('title', 'cs') === $title;
        })->first();

        if ($pool) {
            $this->line(" - Nalezen existující pool: {$pool->id}");
            return $pool;
        }

        if ($this->option('dry-run')) {
            $this->line(" - [Dry-run] Vytvořil bych nový pool: {$title}");
            return null; // V dry-runu nevracíme model pokud neexistuje
        }

        // Vytvoříme nový pool
        $pool = new PhotoPool();
        $pool->setTranslation('title', 'cs', $title);
        $pool->setTranslation('title', 'en', $title); // Zatím stejné
        $pool->slug = $slug;
        $pool->description = ['cs' => $this->decode($oldGallery->popis), 'en' => ''];
        $pool->event_date = $oldGallery->datum ? date('Y-m-d', (int)$oldGallery->datum) : null;
        $pool->is_public = true;
        $pool->is_visible = true;
        $pool->save();

        // Přidáme do cache, aby další cykly o něm věděly (např. při root importu)
        $this->poolsCache->push($pool);

        $this->info(" - Vytvořen nový pool: {$pool->id}");

        return $pool;
    }

    /**
     * Zpracuje fotografie.
     */
    protected function processPhotos(PhotoPool $pool, array $files, $oldGalleryId = null): void
    {
        $count = count($files);
        $this->line(" - Celkem k importu: {$count} souborů");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($files as $file) {
            if ($this->isNahled($file)) {
                $bar->advance();
                continue;
            }

            $filename = $file->getFilename();

            // Kontrola duplicity: má už tento pool tuto fotku?
            // Můžeme kontrolovat podle názvu souboru v media library
            $exists = $pool->mediaAssets()
                ->whereHas('media', function($query) use ($filename) {
                    $query->where('name', pathinfo($filename, PATHINFO_FILENAME));
                })->exists();

            if ($exists) {
                $bar->advance();
                continue;
            }

            if ($this->option('dry-run')) {
                $bar->advance();
                continue;
            }

            try {
                DB::transaction(function() use ($pool, $file, $filename) {
                    $asset = new MediaAsset([
                        'title' => pathinfo($filename, PATHINFO_FILENAME),
                        'type' => 'image',
                        'access_level' => 'public',
                        'is_public' => true,
                    ]);
                    $asset->save();

                    $lastSort = $pool->mediaAssets()->max('sort_order') ?? 0;
                    $pool->mediaAssets()->attach($asset->id, [
                        'sort_order' => $lastSort + 1,
                        'is_visible' => true,
                    ]);

                    $asset->addMedia($file->getPathname())
                        ->preservingOriginal()
                        ->toMediaCollection('default');
                });
            } catch (\Exception $e) {
                $this->error("\nChyba při importu {$filename}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line("");
    }

    /**
     * Importuje fotografie v root složce fotoalbum.
     */
    protected function importRootPhotos(): void
    {
        $files = File::files($this->oldFotoPath);
        if (empty($files)) return;

        $this->info("Zpracovávám fotografie v rootu fotoalba...");

        $poolTitle = 'Fotografie z archivu';
        $pool = $this->findOrCreatePool((object)['popis' => '', 'datum' => null], $poolTitle);

        if ($pool) {
            $this->processPhotos($pool, $files);
        }
    }

    /**
     * Dekóduje text z pravděpodobného cp1250/latin2.
     */
    protected function decode($text): string
    {
        if (!$text) return '';
        // Zkusíme převod z UTF-8 na UTF-8 (může to být "double encoded" nebo mít jiné problémy)
        if (mb_check_encoding($text, 'UTF-8')) {
            // Zkusíme detekovat "double encoding" nebo CP1250 interpretované jako UTF-8
            if (str_contains($text, 'Ă')) {
                 $attempt = @iconv('UTF-8', 'ISO-8859-1//IGNORE', $text);
                 if ($attempt) {
                     return iconv('CP1250', 'UTF-8//IGNORE', $attempt) ?: $text;
                 }
            }
            return $text;
        }
        // Jinak zkusíme převod (na starých webech byla často cp1250)
        return iconv('CP1250', 'UTF-8//IGNORE', $text) ?: $text;
    }

    /**
     * Kontroluje, zda jde o náhled/thumbnail.
     */
    protected function isNahled($file): bool
    {
        $path = strtolower($file->getPathname());
        $filename = strtolower($file->getFilename());

        if (str_contains($path, '/nahledy/') || str_contains($path, '/thumbnails/')) {
            return true;
        }

        if ($filename === 'thumbs.db' || $filename === '.ds_store') {
            return true;
        }

        return false;
    }
}

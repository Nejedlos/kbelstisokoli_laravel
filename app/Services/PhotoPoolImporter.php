<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\PhotoPool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoPoolImporter
{
    /**
     * Přesune soubory z incoming do pending složky a uloží je do fronty v modelu.
     */
    public function prepareForImport(PhotoPool $pool, array $filePaths): void
    {
        if (empty($filePaths)) {
            return;
        }

        $diskName = config('filesystems.uploads.disk');
        $disk = Storage::disk($diskName);
        $uploadsDir = trim(config('filesystems.uploads.dir', 'uploads'), '/');

        // Cílová složka pro originály
        $targetBase = "{$uploadsDir}/photo_pools/{$pool->id}/originals";
        if (! $disk->exists($targetBase)) {
            $disk->makeDirectory($targetBase);
        }

        $currentQueue = $pool->pending_import_queue ?? [];

        foreach ($filePaths as $path) {
            if (! $disk->exists($path)) {
                continue;
            }

            $filename = basename($path);
            $targetPath = $targetBase.'/'.$filename;

            // Pokud už tam soubor není, přesuneme ho
            if ($path !== $targetPath) {
                // Pokud už existuje cílový soubor, přidáme k němu náhodný string pro unikátnost
                if ($disk->exists($targetPath)) {
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $name = pathinfo($filename, PATHINFO_FILENAME);
                    $filename = $name.'-'.Str::random(5).'.'.$ext;
                    $targetPath = $targetBase.'/'.$filename;
                }
                $disk->move($path, $targetPath);
            }

            $currentQueue[] = $targetPath;
        }

        $pool->updateQuietly([
            'pending_import_queue' => $currentQueue,
        ]);
    }

    /**
     * Zpracuje další dávku z fronty.
     */
    public function processChunk(PhotoPool $pool, int $chunkSize = 10, ?int $userId = null): array
    {
        $queue = $pool->pending_import_queue ?? [];
        if (empty($queue)) {
            $pool->updateQuietly(['is_processing_import' => false]);

            return ['processed' => 0, 'remaining' => 0];
        }

        $pool->updateQuietly(['is_processing_import' => true]);

        $chunk = array_slice($queue, 0, $chunkSize);
        $remaining = array_slice($queue, $chunkSize);

        $diskName = config('filesystems.uploads.disk');
        $disk = Storage::disk($diskName);

        // Zjistíme aktuální maximální pořadí v galerii, abychom na něj mohli navázat
        $lastSort = DB::table('photo_pool_media_asset')
            ->where('photo_pool_id', $pool->id)
            ->max('sort_order') ?? 0;

        $sort = $lastSort;
        $processed = 0;

        foreach ($chunk as $path) {
            try {
                if (! $disk->exists($path)) {
                    $processed++;

                    continue;
                }

                $fullPath = $disk->path($path);
                $file = new \Illuminate\Http\File($fullPath);
                $sort++;

                DB::transaction(function () use ($pool, $file, $sort, $userId) {
                    $asset = new MediaAsset([
                        'title' => (string) (brand_text($pool->getTranslation('title', 'cs')).' #'.$sort),
                        'alt_text' => brand_text($pool->getTranslation('title', 'cs')),
                        'type' => 'image',
                        'access_level' => 'public',
                        'is_public' => true,
                        'uploaded_by_id' => $userId,
                    ]);
                    $asset->save();

                    $pool->mediaAssets()->attach($asset->id, [
                        'sort_order' => $sort,
                        'is_visible' => true,
                    ]);

                    $asset->addMedia($file)->toMediaCollection('default');
                });

                // Po úspěšném zpracování a uložení do MediaLibrary můžeme původní soubor smazat
                $disk->delete($path);

                $processed++;
            } catch (\Throwable $e) {
                \Log::error('PhotoPool batch import failed: '.$e->getMessage(), [
                    'pool_id' => $pool->id,
                    'path' => $path,
                ]);
            }
        }

        $pool->updateQuietly([
            'pending_import_queue' => $remaining,
            'is_processing_import' => ! empty($remaining),
        ]);

        return [
            'processed' => $processed,
            'remaining' => count($remaining),
        ];
    }
}

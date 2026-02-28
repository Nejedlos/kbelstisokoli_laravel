<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\MediaAsset;
use App\Models\PhotoPool;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreatePhotoPool extends CreateRecord
{
    protected static string $resource = PhotoPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getFormActions(): array
    {
        return parent::getFormActions();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return parent::render();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Vygenerujeme slug pokud chybí
        if (empty($data['slug']) && ! empty($data['title'])) {
            $base = Str::slug(is_string($data['title']) ? $data['title'] : (string) (\Illuminate\Support\Arr::get($data['title'], app()->getLocale()) ?? ''));
            $slug = $base;
            $i = 1;
            while (PhotoPool::where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Zpracujeme nahrané soubory a vytvoříme MediaAsset + pivot
        $files = $this->form->getRawState()['photos'] ?? [];
        if (empty($files)) {
            return;
        }

        /** @var PhotoPool $pool */
        $pool = $this->record;
        $uploaderId = auth()->id();

        DB::transaction(function () use ($files, $pool, $uploaderId) {
            // Zvýšení časového limitu pro dlouhé zpracování fotek
            @set_time_limit(300);

            $total = count($files);
            $sort = 0;
            $this->stream('ks-loader-progress', '', true);
            $this->stream('ks-loader-progress-text', '', true);

            $diskName = config('filesystems.uploads.disk');
            $disk = Storage::disk($diskName);
            $uploadsDir = trim(config('filesystems.uploads.dir', 'uploads'), '/');
            $targetBase = "{$uploadsDir}/photo_pools/{$pool->id}/originals";

            // Zajistíme existenci cílové složky pro originály
            if (! $disk->exists($targetBase)) {
                $disk->makeDirectory($targetBase);
            }

            foreach ($files as $path) {
                try {
                    $sort++;
                    $progressMsg = __("admin.navigation.resources.photo_pool.notifications.processing")." ($sort / $total)";
                    $this->stream('ks-loader-progress', " ($sort / $total)");
                    $this->stream('ks-loader-progress-text', $progressMsg);

                    if (! $disk->exists($path)) {
                        continue;
                    }

                    // Přesun z incoming do trvalé složky pro originály
                    $filename = basename($path);
                    $targetPath = $targetBase . '/' . $filename;

                    // Pokud už tam soubor není (např. při re-runu), přesuneme ho
                    if ($path !== $targetPath) {
                        $disk->move($path, $targetPath);
                    }

                    $fullPath = $disk->path($targetPath);
                    $file = new \Illuminate\Http\File($fullPath);

                    $asset = new MediaAsset([
                        'title' => (string) (brand_text($pool->getTranslation('title', 'cs')).' #'.($sort)),
                        'alt_text' => brand_text($pool->getTranslation('title', 'cs')),
                        'type' => 'image',
                        'access_level' => 'public',
                        'is_public' => true,
                        'uploaded_by_id' => $uploaderId,
                    ]);
                    $asset->save();

                    // Napojení do poolu hned po uložení assetu (aby path generator mohl v DB vidět vazbu na pool)
                    $pool->mediaAssets()->attach($asset->id, [
                        'sort_order' => $sort,
                        'is_visible' => true,
                        'caption_override' => null,
                    ]);

                    $asset
                        ->addMedia($file)
                        ->toMediaCollection('default');
                } catch (\Throwable $e) {
                    \Log::error('Photo import failed in afterCreate: '.$e->getMessage());
                }
            }
        });
    }
}

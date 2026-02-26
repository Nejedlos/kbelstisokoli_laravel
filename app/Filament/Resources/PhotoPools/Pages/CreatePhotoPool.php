<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\MediaAsset;
use App\Models\PhotoPool;
use App\Services\AiTextEnhancer;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Vygenerujeme slug pokud chybí
        if (empty($data['slug']) && !empty($data['title'])) {
            $base = Str::slug(is_string($data['title']) ? $data['title'] : (string) (\Illuminate\Support\Arr::get($data['title'], app()->getLocale()) ?? ''));
            $slug = $base;
            $i = 1;
            while (PhotoPool::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Zpracujeme nahrané soubory a vytvoříme MediaAsset + pivot
        $files = $this->form->getState()['photos'] ?? [];
        if (empty($files)) {
            return;
        }

        /** @var PhotoPool $pool */
        $pool = $this->record;
        $uploaderId = auth()->id();

        DB::transaction(function () use ($files, $pool, $uploaderId) {
            $sort = 0;
            foreach ($files as $path) {
                try {
                    $fullPath = Storage::disk(config('filesystems.default'))->path($path);
                    $file = new \Illuminate\Http\File($fullPath);

                    $asset = new MediaAsset([
                        'title' => (string) (brand_text($pool->title) . ' #' . (++$sort)),
                        'alt_text' => brand_text($pool->title),
                        'type' => 'image',
                        'access_level' => 'public',
                        'is_public' => true,
                        'uploaded_by_id' => $uploaderId,
                    ]);
                    $asset->save();

                    $asset
                        ->addMedia($file)
                        ->toMediaCollection('default');

                    // Napojení do poolu
                    $pool->mediaAssets()->attach($asset->id, [
                        'sort_order' => $sort,
                        'is_visible' => true,
                        'caption_override' => null,
                    ]);
                } catch (\Throwable $e) {
                    // Pokud se jedna fotka nepovede, pokračujeme dál, ale zalogujeme
                    \Log::warning('Photo import failed: ' . $e->getMessage());
                }
            }
        });
    }
}

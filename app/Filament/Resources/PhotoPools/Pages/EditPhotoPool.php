<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\MediaAsset;
use App\Models\PhotoPool;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EditPhotoPool extends EditRecord
{
    protected static string $resource = PhotoPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Zpracujeme nahrané soubory a vytvoříme MediaAsset + pivot
        // Stejná logika jako v CreatePhotoPool, aby šlo doplňovat fotky i při editaci
        $state = $this->form->getRawState();
        $files = $state['photos'] ?? [];

        if (empty($files)) {
            return;
        }

        /** @var PhotoPool $pool */
        $pool = $this->record;
        $uploaderId = auth()->id();

        DB::transaction(function () use ($files, $pool, $uploaderId) {
            $lastSort = $pool->mediaAssets()->max('sort_order') ?? 0;
            $sort = $lastSort;

            foreach ($files as $path) {
                try {
                    $diskName = config('filesystems.uploads.disk');
                    $disk = Storage::disk($diskName);

                    if (! $disk->exists($path)) {
                        continue;
                    }

                    $fullPath = $disk->path($path);
                    $file = new \Illuminate\Http\File($fullPath);

                    $asset = new MediaAsset([
                        'title' => (string) (brand_text($pool->getTranslation('title', 'cs')).' #'.(++$sort)),
                        'alt_text' => brand_text($pool->getTranslation('title', 'cs')),
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
                    \Log::error('Photo import failed in afterSave: '.$e->getMessage());
                }
            }
        });

        // Vyčistíme pole photos ve formuláři bez resetu celého formuláře
        $this->data['photos'] = [];

        // Refreshneme Relation Manager, aby se fotky hned zobrazily
        $this->dispatch('refreshRelationManager', relationship: 'mediaAssets');
    }
}

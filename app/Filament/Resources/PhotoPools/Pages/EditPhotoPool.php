<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\MediaAsset;
use App\Models\PhotoPool;
use Filament\Actions\Action;
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
        $state = $this->form->getState();
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

                    // Po zpracování smažeme dočasný soubor (volitelné, Filament to většinou pořeší, ale jistota je jistota)
                    // Storage::disk(config('filesystems.default'))->delete($path);
                } catch (\Throwable $e) {
                    \Log::warning('Photo import failed during edit: ' . $e->getMessage());
                }
            }
        });

        // Vyčistíme pole photos ve formuláři, aby se neukládalo znovu při dalším save
        $this->form->fill(['photos' => []]);
    }
}

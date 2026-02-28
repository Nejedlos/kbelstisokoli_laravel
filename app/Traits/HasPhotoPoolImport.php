<?php

namespace App\Traits;

use App\Services\PhotoPoolImporter;
use Filament\Notifications\Notification;

trait HasPhotoPoolImport
{
    public bool $confirmingCancellation = false;

    public function cancelImportQueue(): void
    {
        $this->confirmingCancellation = true;
    }

    public function dismissCancelImportQueue(): void
    {
        $this->confirmingCancellation = false;
    }

    public function confirmCancelImportQueue(): void
    {
        /** @var \App\Models\PhotoPool $record */
        $record = $this->getRecord();

        if (! $record) {
            $this->confirmingCancellation = false;

            return;
        }

        $queue = $record->pending_import_queue ?? [];

        if (! empty($queue)) {
            $diskName = config('filesystems.uploads.disk');
            $disk = \Illuminate\Support\Facades\Storage::disk($diskName);
            $deletedCount = 0;

            foreach ($queue as $path) {
                try {
                    if ($disk->exists($path)) {
                        $disk->delete($path);
                        $deletedCount++;
                    }
                } catch (\Throwable $e) {
                    \Log::error('Chyba při mazání souboru z fronty importu: '.$e->getMessage(), [
                        'path' => $path,
                        'pool_id' => $record->id,
                    ]);
                }
            }

            \Log::info("Import PhotoPoolu #{$record->id} byl přerušen. Smazáno {$deletedCount} souborů z fronty.");
        }

        // Vyčistíme frontu a zastavíme import
        $record->updateQuietly([
            'pending_import_queue' => [],
            'is_processing_import' => false,
        ]);

        $this->confirmingCancellation = false;

        Notification::make()
            ->title('Import přerušen')
            ->warning()
            ->body('Zpracování zbývajících fotografií bylo zrušeno a dočasné soubory byly odstraněny.')
            ->send();

        // Refreshneme Relation Manager
        $this->dispatch('refreshRelationManager', relationship: 'mediaAssets');
    }

    public function processImportQueue(): void
    {
        /** @var \App\Models\PhotoPool $record */
        $record = $this->getRecord();

        if (! $record) {
            return;
        }

        // Pokud je fronta prázdná, už nic neděláme
        if (empty($record->pending_import_queue)) {
            if ($record->is_processing_import) {
                $record->updateQuietly(['is_processing_import' => false]);
            }

            return;
        }

        $importer = app(PhotoPoolImporter::class);
        $result = $importer->processChunk($record, 5, auth()->id());

        // Refreshneme data v modelu, aby UI vidělo novou frontu
        $record->refresh();

        // Refreshneme Relation Manager, aby se nové fotky hned zobrazily
        $this->dispatch('refreshRelationManager', relationship: 'mediaAssets');

        if ($result['remaining'] === 0) {
            Notification::make()
                ->title('Import dokončen')
                ->success()
                ->body('Všechny fotografie byly úspěšně zpracovány.')
                ->send();
        }
    }
}

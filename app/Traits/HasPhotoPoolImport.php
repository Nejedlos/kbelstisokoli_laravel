<?php

namespace App\Traits;

use App\Services\PhotoPoolImporter;
use Filament\Notifications\Notification;

trait HasPhotoPoolImport
{
    public function cancelImportQueue(): void
    {
        /** @var \App\Models\PhotoPool $record */
        $record = $this->getRecord();

        if (! $record) {
            return;
        }

        $queue = $record->pending_import_queue ?? [];

        if (! empty($queue)) {
            $diskName = config('filesystems.uploads.disk');
            $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

            foreach ($queue as $path) {
                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
            }
        }

        // Vyčistíme frontu a zastavíme import
        $record->updateQuietly([
            'pending_import_queue' => [],
            'is_processing_import' => false,
        ]);

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

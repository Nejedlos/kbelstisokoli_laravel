<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\PhotoPool;
use App\Traits\HasPhotoPoolImport;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPhotoPool extends EditRecord
{
    use HasPhotoPoolImport;

    protected static string $resource = PhotoPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
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

    protected function afterSave(): void
    {
        // Připravíme nahrané soubory do fronty pro postupné zpracování
        $state = $this->form->getRawState();
        $files = $state['photos'] ?? [];

        if (empty($files)) {
            return;
        }

        /** @var PhotoPool $pool */
        $pool = $this->record;

        // Použijeme službu pro přípravu importu (přesun souborů a naplnění fronty)
        $importer = app(\App\Services\PhotoPoolImporter::class);
        $importer->prepareForImport($pool, $files);

        // Vyčistíme pole photos ve formuláři bez resetu celého formuláře
        $this->data['photos'] = [];

        // Informujeme uživatele
        \Filament\Notifications\Notification::make()
            ->title(__('admin.navigation.resources.photo_pool.notifications.uploading'))
            ->info()
            ->body('Fotografie byly nahrány a zařazeny do fronty ke zpracování.')
            ->send();
    }
}

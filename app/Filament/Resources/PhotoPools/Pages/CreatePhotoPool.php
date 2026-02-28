<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\PhotoPool;
use App\Traits\HasPhotoPoolImport;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePhotoPool extends CreateRecord
{
    use HasPhotoPoolImport;

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

        // Informujeme uživatele
        \Filament\Notifications\Notification::make()
            ->title(__('admin.navigation.resources.photo_pool.notifications.uploading'))
            ->info()
            ->body('Fotografie byly nahrány a zařazeny do fronty ke zpracování.')
            ->send();
    }
}

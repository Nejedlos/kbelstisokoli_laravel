<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\PhotoPool;
use App\Traits\HasPhotoPoolImport;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

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
        return [
            $this->getCreateFormAction()
                ->label(new \Illuminate\Support\HtmlString('
                    <span x-data="{ uploadingCount: 0 }"
                          x-on:file-upload-started.window="uploadingCount++"
                          x-on:file-upload-finished.window="uploadingCount = Math.max(0, uploadingCount - 1)">
                        <span x-show="uploadingCount === 0">
                            <i class="fa-light fa-plus mr-1.5"></i> ' . $this->getCreateFormAction()->getLabel() . '
                        </span>
                        <span x-show="uploadingCount > 0" x-cloak class="flex items-center gap-2">
                            <i class="fa-light fa-arrows-rotate fa-spin"></i>
                            Počkej na nahrání všech fotografií... (<span x-text="uploadingCount"></span>)
                        </span>
                    </span>
                '))
                ->extraAttributes([
                    'x-data' => '{ uploadingCount: 0 }',
                    'x-on:file-upload-started.window' => 'uploadingCount++',
                    'x-on:file-upload-finished.window' => 'uploadingCount = Math.max(0, uploadingCount - 1)',
                    'x-bind:disabled' => 'uploadingCount > 0',
                    'x-bind:class' => "{ 'opacity-70 cursor-not-allowed': uploadingCount > 0 }",
                ]),
            $this->getCancelFormAction(),
        ];
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
            ->title(__('admin.resources.photo_pool.notifications.uploading'))
            ->info()
            ->body('Fotografie byly nahrány a zařazeny do fronty ke zpracování.')
            ->send();
    }
}

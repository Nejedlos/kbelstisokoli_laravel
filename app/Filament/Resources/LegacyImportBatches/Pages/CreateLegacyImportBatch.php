<?php

namespace App\Filament\Resources\LegacyImportBatches\Pages;

use App\Filament\Resources\LegacyImportBatches\LegacyImportBatchResource;
use App\Services\Stats\Legacy\LegacyImportService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLegacyImportBatch extends CreateRecord
{
    protected static string $resource = LegacyImportBatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id();
        $data['status'] = 'queued';

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getRawState();
        $files = $data['uploaded_files'] ?? [];

        if (! empty($files)) {
            $service = app(LegacyImportService::class);
            $service->processUploads($this->record, $files);
        }
    }
}

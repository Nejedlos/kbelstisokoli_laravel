<?php

namespace App\Filament\Resources\LegacyImportBatches\Pages;

use App\Filament\Resources\LegacyImportBatches\LegacyImportBatchResource;
use App\Services\Stats\Legacy\LegacyImportService;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewLegacyImportBatch extends ViewRecord
{
    protected static string $resource = LegacyImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startImport')
                ->label('Spustit import')
                ->icon('fa-light.fa-play')
                ->color('success')
                ->action(fn (LegacyImportService $service) => $service->startBatch($this->record))
                ->visible(fn () => in_array($this->record->status, ['queued', 'failed', 'partial_failed'])),

            Action::make('reRunFailed')
                ->label('Znovu spustit chyby')
                ->icon('fa-light.fa-arrows-rotate')
                ->color('warning')
                ->action(function (LegacyImportService $service) {
                    $this->record->files()->where('status', 'failed')->update(['status' => 'queued']);
                    $service->startBatch($this->record);
                })
                ->visible(fn () => $this->record->failed_files > 0),
        ];
    }
}

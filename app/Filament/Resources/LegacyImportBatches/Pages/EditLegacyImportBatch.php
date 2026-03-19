<?php

namespace App\Filament\Resources\LegacyImportBatches\Pages;

use App\Filament\Resources\LegacyImportBatches\LegacyImportBatchResource;
use App\Services\Stats\Legacy\LegacyImportService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLegacyImportBatch extends EditRecord
{
    protected static string $resource = LegacyImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startImport')
                ->label('Spustit import')
                ->icon(FilamentIcon::get(AppIcon::PLAY))
                ->color('success')
                ->action(fn (LegacyImportService $service) => $service->startBatch($this->record))
                ->visible(fn () => in_array($this->record->status, ['queued', 'failed', 'partial_failed'])),

            Action::make('reRunFailed')
                ->label('Znovu spustit chyby')
                ->icon(FilamentIcon::get(AppIcon::REFRESH))
                ->color('warning')
                ->action(function (LegacyImportService $service) {
                    $this->record->files()->where('status', 'failed')->update(['status' => 'queued']);
                    $service->startBatch($this->record);
                })
                ->visible(fn () => $this->record->failed_files > 0),

            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PerformanceTestResults\Pages;

use App\Filament\Resources\PerformanceTestResults\PerformanceTestResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPerformanceTestResult extends EditRecord
{
    protected static string $resource = PerformanceTestResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

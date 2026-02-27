<?php

namespace App\Filament\Resources\PerformanceTestResults\Pages;

use App\Filament\Resources\PerformanceTestResults\PerformanceTestResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPerformanceTestResults extends ListRecords
{
    protected static string $resource = PerformanceTestResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

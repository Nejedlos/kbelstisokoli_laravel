<?php

namespace App\Filament\Resources\PerformanceTestResults\Pages;

use App\Filament\Resources\PerformanceTestResults\PerformanceTestResultResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerformanceTestResult extends CreateRecord
{
    protected static string $resource = PerformanceTestResultResource::class;
}

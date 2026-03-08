<?php

namespace App\Filament\Resources\ExternalEntityMappings\Pages;

use App\Filament\Resources\ExternalEntityMappings\ExternalEntityMappingResource;
use App\Filament\Resources\ExternalEntityMappings\Widgets\ExternalMappingInfoWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExternalEntityMappings extends ListRecords
{
    protected static string $resource = ExternalEntityMappingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExternalMappingInfoWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}

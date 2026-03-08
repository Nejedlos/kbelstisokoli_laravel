<?php

namespace App\Filament\Resources\ExternalEntityMappings\Widgets;

use Filament\Widgets\Widget;

class ExternalMappingInfoWidget extends Widget
{
    protected string $view = 'filament.resources.external-entity-mappings.info-alert';

    protected int | string | array $columnSpan = 'full';
}

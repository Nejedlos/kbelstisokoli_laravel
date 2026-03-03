<?php

namespace App\Filament\Resources\ExternalTeamSeasonConfigs\Pages;

use App\Filament\Resources\ExternalTeamSeasonConfigs\ExternalTeamSeasonConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExternalTeamSeasonConfig extends EditRecord
{
    protected static string $resource = ExternalTeamSeasonConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

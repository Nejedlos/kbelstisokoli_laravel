<?php

namespace App\Filament\Resources\UserSeasonConfigs\Pages;

use App\Filament\Resources\UserSeasonConfigs\UserSeasonConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserSeasonConfig extends EditRecord
{
    protected static string $resource = UserSeasonConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

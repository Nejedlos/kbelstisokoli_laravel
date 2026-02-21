<?php

namespace App\Filament\Resources\PlayerProfiles\Pages;

use App\Filament\Resources\PlayerProfiles\PlayerProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlayerProfile extends EditRecord
{
    protected static string $resource = PlayerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

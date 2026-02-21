<?php

namespace App\Filament\Resources\ClubEvents\Pages;

use App\Filament\Resources\ClubEvents\ClubEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClubEvent extends EditRecord
{
    protected static string $resource = ClubEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Dmarc\DmarcMailboxResource\Pages;

use App\Filament\Resources\Dmarc\DmarcMailboxResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMailboxes extends ListRecords
{
    protected static string $resource = DmarcMailboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

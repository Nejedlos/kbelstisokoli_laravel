<?php

namespace App\Filament\Resources\Dmarc\DmarcMailboxResource\Pages;

use App\Filament\Resources\Dmarc\DmarcMailboxResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMailbox extends EditRecord
{
    protected static string $resource = DmarcMailboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

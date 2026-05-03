<?php

namespace App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Pages;

use App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDmarcAuthorizedSender extends EditRecord
{
    protected static string $resource = DmarcAuthorizedSenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

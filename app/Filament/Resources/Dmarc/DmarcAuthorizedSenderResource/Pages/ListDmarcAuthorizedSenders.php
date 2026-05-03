<?php

namespace App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Pages;

use App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDmarcAuthorizedSenders extends ListRecords
{
    protected static string $resource = DmarcAuthorizedSenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

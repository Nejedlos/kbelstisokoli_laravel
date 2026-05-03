<?php

namespace App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Pages;

use App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDmarcAuthorizedSender extends CreateRecord
{
    protected static string $resource = DmarcAuthorizedSenderResource::class;
}

<?php

namespace App\Filament\Resources\Dmarc\DmarcReportResource\Pages;

use App\Filament\Resources\Dmarc\DmarcReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = DmarcReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

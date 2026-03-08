<?php

namespace App\Filament\Resources\FeedbackReports\Pages;

use App\Filament\Resources\FeedbackReports\FeedbackReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackReports extends ListRecords
{
    protected static string $resource = FeedbackReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

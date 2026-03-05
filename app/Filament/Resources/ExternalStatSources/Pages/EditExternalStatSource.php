<?php

namespace App\Filament\Resources\ExternalStatSources\Pages;

use App\Filament\Resources\ExternalStatSources\ExternalStatSourceResource;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExternalStatSource extends EditRecord
{
    protected static string $resource = ExternalStatSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Synchronizovat vše')
                ->icon(IconHelper::render(IconHelper::REFRESH))
                ->color('success')
                ->action(function () {
                    // Tady by se v budoucnu volal IngestPipeline
                    Notification::make()
                        ->title('Synchronizace spuštěna')
                        ->body('Proces synchronizace byl zařazen do fronty.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}

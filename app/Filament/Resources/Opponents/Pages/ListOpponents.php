<?php

namespace App\Filament\Resources\Opponents\Pages;

use App\Filament\Resources\Opponents\OpponentResource;
use App\Filament\Resources\OpponentMergeSuggestions\OpponentMergeSuggestionResource;
use App\Services\Stats\OpponentMergeService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListOpponents extends ListRecords
{
    protected static string $resource = OpponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan_duplicates')
                ->label(__('admin.resources.opponent_merge_suggestion.actions.scan'))
                ->icon('heroicon-o-magnifying-glass')
                ->action(function (OpponentMergeService $service) {
                    $count = $service->scan(false);

                    Notification::make()
                        ->title(__('admin.resources.opponent_merge_suggestion.notifications.scan_finished', ['count' => $count]))
                        ->success()
                        ->send();

                    return redirect(OpponentMergeSuggestionResource::getUrl());
                }),
            Action::make('hard_scan_duplicates')
                ->label(__('admin.resources.opponent_merge_suggestion.actions.hard_scan'))
                ->icon('heroicon-o-magnifying-glass-plus')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (OpponentMergeService $service) {
                    $count = $service->scan(true);

                    Notification::make()
                        ->title(__('admin.resources.opponent_merge_suggestion.notifications.hard_scan_finished', ['count' => $count]))
                        ->success()
                        ->send();

                    return redirect(OpponentMergeSuggestionResource::getUrl());
                }),
            CreateAction::make(),
        ];
    }
}

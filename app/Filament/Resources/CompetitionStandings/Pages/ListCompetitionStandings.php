<?php

namespace App\Filament\Resources\CompetitionStandings\Pages;

use App\Filament\Resources\CompetitionStandings\CompetitionStandingResource;
use App\Models\Season;
use App\Services\Stats\Sync\CompetitionSyncService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCompetitionStandings extends ListRecords
{
    protected static string $resource = CompetitionStandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncStandings')
                ->label('Synchronizovat tabulky')
                ->icon(FilamentIcon::get(AppIcon::REFRESH))
                ->color('success')
                ->form([
                    Select::make('seasonId')
                        ->label('Sezóna')
                        ->options(Season::orderBy('name', 'desc')->pluck('name', 'id'))
                        ->default(Season::where('is_active', true)->value('id'))
                        ->required(),
                ])
                ->action(function (array $data, CompetitionSyncService $syncService) {
                    $season = Season::find($data['seasonId']);
                    if (!$season) return;

                    try {
                        $syncService->syncAllStandings($season);

                        Notification::make()
                            ->title('Tabulky synchronizovány')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Chyba při synchronizaci')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}

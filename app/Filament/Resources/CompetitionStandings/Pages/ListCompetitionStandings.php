<?php

namespace App\Filament\Resources\CompetitionStandings\Pages;

use App\Filament\Resources\CompetitionStandings\CompetitionStandingResource;
use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Sync\CompetitionSyncService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCompetitionStandings extends ListRecords
{
    protected static string $resource = CompetitionStandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncStandings')
                ->label('Synchronizovat')
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

                    // Zjistit aktivní tab (tým)
                    $tabKey = request()->query('tab');
                    $teamId = null;
                    if (is_string($tabKey) && str_starts_with($tabKey, 'team-')) {
                        $teamId = (int) str_replace('team-', '', $tabKey);
                    }

                    try {
                        if ($teamId) {
                            // Sync pouze soutěže daného týmu v dané sezóně
                            $urls = ExternalTeamSeasonConfig::query()
                                ->where('season_id', $season->id)
                                ->where('team_id', $teamId)
                                ->where('is_enabled', true)
                                ->whereNotNull('competition_url')
                                ->pluck('competition_url')
                                ->filter()
                                ->unique();

                            foreach ($urls as $url) {
                                $syncService->syncStandingsOnly($url, $season);
                            }
                        } else {
                            // Jinak: všechny soutěže pro sezónu
                            $syncService->syncAllStandings($season);
                        }

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
        ];
    }
    public function getTabs(): array
    {
        $tabs = [];

        // Výchozí tab: všechny týmy
        $tabs['all'] = Tab::make('Všechny týmy')
            ->modifyQueryUsing(function (Builder $query) {
                // Bez omezení týmu; pouze aplikované filtry tabulky (např. sezóna)
                return $query;
            });

        // Tabs po týmech (abecedně)
        $teams = Team::orderBy('name')->get(['id', 'name']);
        foreach ($teams as $team) {
            $tabs['team-' . $team->id] = Tab::make($team->name)
                ->modifyQueryUsing(function (Builder $query) use ($team) {
                    $seasonId = request()->query('tableFilters')['season_id']['value'] ?? null;

                    $urlsQuery = ExternalTeamSeasonConfig::query()
                        ->where('team_id', $team->id)
                        ->where('is_enabled', true)
                        ->whereNotNull('competition_url');

                    if ($seasonId) {
                        $urlsQuery->where('season_id', $seasonId);
                    }

                    $competitionUrls = $urlsQuery->pluck('competition_url')->filter()->unique();

                    if ($competitionUrls->isEmpty()) {
                        // Žádné soutěže pro tento tým; vrať prázdný výsledek
                        return $query->whereRaw('1 = 0');
                    }

                    return $query->whereIn('competition_url', $competitionUrls);
                });
        }

        return $tabs;
    }
}

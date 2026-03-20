<?php

namespace App\Filament\Resources\CompetitionStandings\Tables;

use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Sync\CompetitionSyncService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CompetitionStandingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rank')
                    ->label('Pořadí')
                    ->sortable(),
                TextColumn::make('team_name')
                    ->label('Tým')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('gp')
                    ->label('Z')
                    ->tooltip('Zápasy')
                    ->sortable(),
                TextColumn::make('w')
                    ->label('V')
                    ->tooltip('Výhry')
                    ->sortable(),
                TextColumn::make('l')
                    ->label('P')
                    ->tooltip('Prohry')
                    ->sortable(),
                TextColumn::make('score')
                    ->label('Skóre')
                    ->formatStateUsing(fn ($state) => \App\Support\MatchResultHelper::formatScore($state))
                    ->sortable(),
                TextColumn::make('points')
                    ->label('B')
                    ->tooltip('Body')
                    ->sortable(),
                TextColumn::make('competition_name')
                    ->label('Soutěž')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('season.name')
                    ->label('Sezóna')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('rank', 'asc')
            ->filters([
                SelectFilter::make('season_id')
                    ->label('Sezóna')
                    ->options(Season::pluck('name', 'id'))
                    ->default(fn () => Season::where('is_active', true)->first()?->id),
                SelectFilter::make('team_id')
                    ->label('Náš tým')
                    ->placeholder('Všechny soutěže')
                    ->options(Team::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        $teamId = $data['value'];
                        $seasonId = request()->query('tableFilters')['season_id']['value'] ?? null;

                        if (!$seasonId) {
                            // Pokud není sezóna, zkusíme najít jakoukoli soutěž toho týmu
                            $competitionUrls = \App\Models\ExternalTeamSeasonConfig::where('team_id', $teamId)
                                ->pluck('competition_url')
                                ->filter()
                                ->unique();
                        } else {
                            $competitionUrls = \App\Models\ExternalTeamSeasonConfig::where('team_id', $teamId)
                                ->where('season_id', $seasonId)
                                ->pluck('competition_url')
                                ->filter()
                                ->unique();
                        }

                        return $query->whereIn('competition_url', $competitionUrls);
                    })
            ])
            ->actions([
                Action::make('syncCompetition')
                    ->label('Synchronizovat')
                    ->icon(FilamentIcon::get(AppIcon::REFRESH))
                    ->color('success')
                    ->action(function ($record, CompetitionSyncService $syncService) {
                        try {
                            $syncService->syncStandingsOnly($record->competition_url, $record->season);

                            Notification::make()
                                ->title('Tabulka soutěže synchronizována')
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

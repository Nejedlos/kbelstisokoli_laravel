<?php

namespace App\Filament\Resources\CompetitionStandings\Tables;

use App\Models\Season;
use App\Models\Team;
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
                //
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

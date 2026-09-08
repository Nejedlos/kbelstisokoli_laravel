<?php

namespace App\Filament\Resources\CompetitionStandings\Tables;

use App\Models\Season;
use App\Support\MatchResultHelper;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->formatStateUsing(fn ($state) => MatchResultHelper::formatScore($state))
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
            ])
            ->actions([])
            ->bulkActions([]);
    }
}

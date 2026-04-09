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
            ])
            ->actions([])
            ->bulkActions([]);
    }
}

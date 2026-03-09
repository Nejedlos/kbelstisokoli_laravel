<?php

namespace App\Filament\Resources\BasketballMatches\Tables;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class BasketballMatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Datum a čas')
                    ->formatStateUsing(fn ($state, $record) => new HtmlString(
                        ((! empty($record->metadata['external_id']) || ! empty($record->metadata['season_external_match_id']))
                            ? '<i class="fa-light fa-cloud-arrow-down fa-fw text-info mr-1" title="Synchronizováno z externího zdroje"></i> '
                            : '').$state->format('d.m.Y H:i')
                    ))
                    ->sortable(),
                TextColumn::make('teams.name')
                    ->label('Týmy')
                    ->badge()
                    ->searchable(query: function ($query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('teams', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        });
                    }),
                TextColumn::make('opponent.name')
                    ->label('Soupeř')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mismatches_count')
                    ->label('Rozpory')
                    ->counts('mismatches')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('score')
                    ->label('Skóre')
                    ->state(fn ($record) => in_array($record->status, ['finished', 'completed', 'played']) ? "{$record->score_home} : {$record->score_away}" : '-')
                    ->badge()
                    ->color(fn ($record): string => match (true) {
                        ! in_array($record->status, ['finished', 'completed', 'played']) => 'gray',
                        $record->score_home === $record->score_away => 'gray',
                        $record->is_home ? ($record->score_home > $record->score_away) : ($record->score_away > $record->score_home) => 'success',
                        default => 'danger',
                    })
                    ->description(fn ($record): ?string => match (true) {
                        ! in_array($record->status, ['finished', 'completed', 'played']) => null,
                        $record->score_home === $record->score_away => __('matches.draw'),
                        $record->is_home ? ($record->score_home > $record->score_away) : ($record->score_away > $record->score_home) => __('matches.victory'),
                        default => __('matches.loss'),
                    }),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planned' => 'info',
                        'scheduled' => 'info',
                        'played' => 'success',
                        'completed' => 'success',
                        'finished' => 'success',
                        'cancelled' => 'danger',
                        'postponed' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Plánováno',
                        'scheduled' => 'Naplánováno',
                        'played' => 'Odehráno',
                        'completed' => 'Odehráno',
                        'finished' => 'Odehráno',
                        'cancelled' => 'Zrušeno',
                        'postponed' => 'Odloženo',
                        default => $state,
                    }),
                IconColumn::make('is_home')
                    ->label('Doma')
                    ->boolean(),
                TextColumn::make('location')
                    ->label('Místo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('teams')
                    ->label('Tým')
                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('season')
                    ->label('Sezóna')
                    ->relationship('season', 'name'),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'planned' => 'Plánováno',
                        'scheduled' => 'Naplánováno',
                        'finished' => 'Odehráno',
                        'played' => 'Odehráno (staré)',
                        'completed' => 'Odehráno (ručně)',
                        'cancelled' => 'Zrušeno',
                        'postponed' => 'Odloženo',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('ai_sync')
                        ->label('AI Synchronizace detailů')
                        ->icon(FilamentIcon::get(AppIcon::AI))
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $records->each(function ($record) {
                                \App\Jobs\Stats\SyncMatchDetailJob::dispatch($record->id, [
                                    'force' => true,
                                    'fresh' => true,
                                    'ai' => true,
                                ]);
                            });
                            Notification::make()->title('AI synchronizace detailů zápasů byla naplánována.')->success()->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\BasketballMatches\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Tým')
                    ->badge()
                    ->searchable(query: function ($query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('team', function ($q) use ($search) {
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
                    ->state(fn ($record) => in_array($record->status, ['completed', 'played']) ? "{$record->score_home} : {$record->score_away}" : '-')
                    ->badge()
                    ->color(fn ($record) => in_array($record->status, ['completed', 'played']) ? ($record->score_home > $record->score_away ? 'success' : 'danger') : 'gray'),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planned' => 'info',
                        'scheduled' => 'info',
                        'played' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'postponed' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Plánováno',
                        'scheduled' => 'Naplánováno',
                        'played' => 'Odehráno',
                        'completed' => 'Odehráno',
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
                SelectFilter::make('team_id')
                    ->label('Tým')
                    ->relationship('team', 'name', fn ($query) => $query->where('category', '!=', 'all'))
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
                        'played' => 'Odehráno',
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
                        ->icon('heroicon-m-sparkles')
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

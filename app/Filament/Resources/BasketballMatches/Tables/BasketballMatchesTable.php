<?php

namespace App\Filament\Resources\BasketballMatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BasketballMatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Datum a čas')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('teams.name')
                    ->label('Týmy')
                    ->badge()
                    ->state(fn ($record) => $record->teams->reject(fn ($team) => $team->category === 'all')->pluck('name'))
                    ->searchable(),
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
                SelectFilter::make('teams')
                    ->label('Tým')
                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                    ->multiple()
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

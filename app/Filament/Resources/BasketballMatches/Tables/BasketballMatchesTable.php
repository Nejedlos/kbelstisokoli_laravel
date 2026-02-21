<?php

namespace App\Filament\Resources\BasketballMatches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class BasketballMatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label('Datum a čas')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Tým')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opponent.name')
                    ->label('Soupeř')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('score')
                    ->label('Skóre')
                    ->state(fn ($record) => $record->status === 'completed' ? "{$record->score_home} : {$record->score_away}" : '-')
                    ->badge()
                    ->color(fn ($record) => $record->status === 'completed' ? ($record->score_home > $record->score_away ? 'success' : 'danger') : 'gray'),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planned' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'postponed' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Plánováno',
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
                SelectFilter::make('team')
                    ->label('Tým')
                    ->relationship('team', 'name'),
                SelectFilter::make('season')
                    ->label('Sezóna')
                    ->relationship('season', 'name'),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'planned' => 'Plánováno',
                        'completed' => 'Odehráno',
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

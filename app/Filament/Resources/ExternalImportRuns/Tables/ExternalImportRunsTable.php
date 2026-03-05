<?php

namespace App\Filament\Resources\ExternalImportRuns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExternalImportRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('run_type')
                    ->label('Typ')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'skipped' => 'gray',
                        'failed' => 'danger',
                        'partial_failed' => 'warning',
                        'running' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Tým')
                    ->placeholder('N/A')
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label('Sezóna')
                    ->sortable(),
                TextColumn::make('imported_count')
                    ->label('Imp.')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('metadata.used_ai_fallback')
                    ->label('AI')
                    ->boolean()
                    ->trueIcon('fas-sparkles')
                    ->trueColor('warning')
                    ->falseIcon('')
                    ->toggleable(),
                TextColumn::make('finished_at')
                    ->label('Trvání')
                    ->getStateUsing(fn ($record) => $record->finished_at ? $record->started_at->diffForHumans($record->finished_at, true) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('run_type')
                    ->label('Typ běhu')
                    ->options([
                        'team_page' => 'Tým (soupiska)',
                        'matches_list' => 'Seznam zápasů',
                        'match_detail' => 'Detail zápasu',
                        'preview' => 'Náhled (preview)',
                    ]),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'success' => 'Úspěch',
                        'skipped' => 'Přeskočeno',
                        'failed' => 'Chyba',
                        'partial_failed' => 'Částečná chyba',
                        'running' => 'Běží',
                    ]),
                SelectFilter::make('team_id')
                    ->label('Tým')
                    ->relationship('team', 'name'),
                SelectFilter::make('season_id')
                    ->label('Sezóna')
                    ->relationship('season', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

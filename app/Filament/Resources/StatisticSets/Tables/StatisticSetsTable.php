<?php

namespace App\Filament\Resources\StatisticSets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatisticSetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->slug),
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'league_table' => 'Ligová tabulka',
                        'player_stats' => 'Hráčské statistiky',
                        'team_summary' => 'Týmový souhrn',
                        'custom_competition' => 'Vlastní',
                        default => $state,
                    }),
                TextColumn::make('source_type')
                    ->label('Zdroj')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manual' => 'Ruční',
                        'external_import' => 'Import',
                        'hybrid' => 'Hybridní',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('is_visible')
                    ->label('Viditelné')
                    ->boolean(),
                TextColumn::make('rows_count')
                    ->label('Počet řádků')
                    ->counts('rows'),
                TextColumn::make('updated_at')
                    ->label('Naposledy upraveno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options([
                        'league_table' => 'Ligová tabulka',
                        'player_stats' => 'Hráčské statistiky',
                        'team_summary' => 'Týmový souhrn',
                        'custom_competition' => 'Vlastní',
                    ]),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'draft' => 'Koncept',
                        'published' => 'Publikováno',
                        'archived' => 'Archivováno',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}

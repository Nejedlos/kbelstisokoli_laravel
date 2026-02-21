<?php

namespace App\Filament\Resources\ClubCompetitions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ClubCompetitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Soutěž')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->metric_description),
                TextColumn::make('season.name')
                    ->label('Sezóna')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'gray',
                        'archived' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Probíhá',
                        'completed' => 'Ukončeno',
                        'archived' => 'Archivováno',
                        default => $state,
                    }),
                TextColumn::make('entries_count')
                    ->label('Počet zápisů')
                    ->counts('entries'),
                IconColumn::make('is_public')
                    ->label('Veřejné')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Poslední aktivita')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('season')
                    ->label('Sezóna')
                    ->relationship('season', 'name'),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'active' => 'Probíhá',
                        'completed' => 'Ukončeno',
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
            ]);
    }
}

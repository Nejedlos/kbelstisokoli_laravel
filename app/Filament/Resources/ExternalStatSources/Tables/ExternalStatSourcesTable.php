<?php

namespace App\Filament\Resources\ExternalStatSources\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExternalStatSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Zdroj')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->source_url),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('teamMappings.team.name')
                    ->label('Přiřazené týmy')
                    ->badge()
                    ->color('success')
                    ->separator(', ')
                    ->url(fn ($record) => $record->teamMappings->count() === 1 ? \App\Filament\Resources\Teams\TeamResource::getUrl('edit', ['record' => $record->teamMappings->first()->team_id]) : null),
                TextColumn::make('source_type')
                    ->label('Typ')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'html_table' => 'HTML Tabulka',
                        'page_extract' => 'Extrakce',
                        'api' => 'API',
                        default => $state,
                    }),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean(),
                TextColumn::make('last_run_at')
                    ->label('Poslední spuštění')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('last_status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
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

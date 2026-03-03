<?php

namespace App\Filament\Resources\ExternalTeamMappings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExternalTeamMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_key')
                    ->label('Zdroj')
                    ->badge()
                    ->color('info'),
                TextColumn::make('team.name')
                    ->label('Interní tým')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('external_team_id')
                    ->label('Externí ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('base_team_url')
                    ->label('Základní URL')
                    ->limit(40)
                    ->url(fn ($record) => $record->base_team_url, true),
                TextColumn::make('updated_at')
                    ->label('Naposledy změněno')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

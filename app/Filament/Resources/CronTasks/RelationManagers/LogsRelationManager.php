<?php

namespace App\Filament\Resources\CronTasks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('started_at')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('started_at')
            ->columns([
                TextColumn::make('started_at')
                    ->label('Start')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->label('Konec')
                    ->dateTime('d.m.Y H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => 'Úspěch',
                        'failed' => 'Chyba',
                        'running' => 'Běží',
                        default => $state,
                    }),
                TextColumn::make('duration_ms')
                    ->label('Trvání')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} ms" : '-')
                    ->sortable(),
                TextColumn::make('output')
                    ->label('Výstup')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('error_message')
                    ->label('Chyba')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('view_output')
                    ->label('Zobrazit výstup')
                    ->modalHeading('Výstup úlohy')
                    ->modalContent(fn ($record) => view('filament.components.cron-output', ['record' => $record]))
                    ->modalSubmitAction(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}

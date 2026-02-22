<?php

namespace App\Filament\Resources\CronLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class CronLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task.name')
                    ->label('Úloha')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Čas spuštění')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
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
                    ->limit(50),
            ])
            ->filters([
                SelectFilter::make('task')
                    ->label('Úloha')
                    ->relationship('task', 'name'),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'success' => 'Úspěch',
                        'failed' => 'Chyba',
                        'running' => 'Běží',
                    ]),
            ])
            ->recordActions([
                Action::make('view_output')
                    ->label('Zobrazit výstup')
                    ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::VIEW))
                    ->modalHeading('Výstup úlohy')
                    ->modalContent(fn ($record) => view('filament.components.cron-output', ['record' => $record]))
                    ->modalSubmitAction(false),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}

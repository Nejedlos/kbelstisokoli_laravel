<?php

namespace App\Filament\Resources\CronTasks\Tables;

use App\Jobs\RunCronTaskJob;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class CronTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('command')
                    ->label('Příkaz')
                    ->fontFamily('monospace')
                    ->searchable(),
                TextColumn::make('expression')
                    ->label('Rozvrh (Cron)')
                    ->fontFamily('monospace'),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean(),
                TextColumn::make('last_run_at')
                    ->label('Naposledy')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('last_status')
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
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('run_now')
                    ->label('Spustit nyní')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        RunCronTaskJob::dispatch($record);
                        Notification::make()
                            ->title('Úloha byla přidána do fronty ke spuštění.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\CronLog;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CronLogsTable extends TableWidget
{
    protected static ?string $heading = 'Historie úloh (Cron Log)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => CronLog::query()->latest('started_at'))
            ->columns([
                TextColumn::make('task.name')
                    ->label('Úloha')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Start')
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
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('error_message')
                    ->label('Chyba')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'success' => 'Úspěch',
                        'failed' => 'Chyba',
                        'running' => 'Běží',
                    ]),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('view_output')
                    ->label('Zobrazit detail')
                    ->icon(IconHelper::get(IconHelper::VIEW))
                    ->modalHeading('Detail průběhu úlohy')
                    ->modalContent(fn (CronLog $record) => view('filament.components.cron-output', ['record' => $record]))
                    ->modalSubmitAction(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

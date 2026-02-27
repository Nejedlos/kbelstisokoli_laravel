<?php

namespace App\Filament\Resources\PerformanceTestResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PerformanceTestResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('section')
                    ->label('Sekce')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'member' => 'info',
                        'admin' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('label')
                    ->label('Stránka')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scenario')
                    ->label('Scénář')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'gray',
                        'aggressive' => 'info',
                        'ultra' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('duration_ms')
                    ->label('Doba (ms)')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state) => $state > 500 ? 'danger' : ($state > 200 ? 'warning' : 'success')),
                TextColumn::make('query_count')
                    ->label('Dotazy')
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state > 50 ? 'danger' : ($state > 20 ? 'warning' : 'success')),
                TextColumn::make('query_time_ms')
                    ->label('Čas DB (ms)')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('memory_mb')
                    ->label('Paměť (MB)')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('opcache_enabled')
                    ->label('Opcache')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('section')
                    ->options([
                        'public' => 'Veřejné',
                        'member' => 'Členské',
                        'admin' => 'Admin',
                    ]),
                SelectFilter::make('scenario')
                    ->options([
                        'standard' => 'Standard',
                        'aggressive' => 'Aggressive',
                        'ultra' => 'Ultra',
                    ]),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

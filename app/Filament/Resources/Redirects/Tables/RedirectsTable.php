<?php

namespace App\Filament\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_path')
                    ->label('Původní cesta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('target')
                    ->label('Cíl')
                    ->state(fn ($record) => $record->target_type === 'internal' ? $record->target_path : $record->target_url)
                    ->description(fn ($record) => $record->target_type === 'internal' ? 'Interní' : 'Externí')
                    ->searchable(['target_path', 'target_url']),

                TextColumn::make('status_code')
                    ->label('Kód')
                    ->badge()
                    ->color(fn (int $state): string => $state === 301 ? 'success' : 'warning')
                    ->sortable(),

                TextColumn::make('match_type')
                    ->label('Shoda')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'exact' => 'Přesná',
                        'prefix' => 'Začíná na',
                        default => $state,
                    }),

                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('hits_count')
                    ->label('Zobrazení')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('last_hit_at')
                    ->label('Naposledy')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktivní'),
                SelectFilter::make('status_code')
                    ->label('Kód')
                    ->options([
                        301 => '301 - Trvalé',
                        302 => '302 - Dočasné',
                    ]),
                SelectFilter::make('match_type')
                    ->label('Typ shody')
                    ->options([
                        'exact' => 'Přesná',
                        'prefix' => 'Začíná na',
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
            ->defaultSort('priority', 'desc');
    }
}

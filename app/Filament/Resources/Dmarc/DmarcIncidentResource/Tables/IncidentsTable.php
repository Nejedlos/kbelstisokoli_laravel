<?php

namespace App\Filament\Resources\Dmarc\DmarcIncidentResource\Tables;

use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class IncidentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('severity')
                    ->label('Závažnost')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Critical' => 'danger',
                        'Warning' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('domain')
                    ->label('Doména')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source_ip')
                    ->label('Zdrojová IP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('occurrences_count')
                    ->label('Výskytů')
                    ->sortable(),
                TextColumn::make('state')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'danger',
                        'ack' => 'warning',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('last_seen_at')
                    ->label('Naposledy')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('state')
                    ->label('Stav')
                    ->options([
                        'open' => 'Otevřeno',
                        'ack' => 'V řešení',
                        'resolved' => 'Vyřešeno',
                    ]),
                SelectFilter::make('severity')
                    ->label('Závažnost')
                    ->options([
                        'Critical' => 'Kritická',
                        'Warning' => 'Varování',
                    ]),
            ])
            ->defaultSort('last_seen_at', 'desc')
            ->actions([
                EditAction::make(),
            ]);
    }
}

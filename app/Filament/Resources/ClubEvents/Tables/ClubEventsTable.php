<?php

namespace App\Filament\Resources\ClubEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ClubEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'social' => 'Společenská',
                        'meeting' => 'Schůzka',
                        'camp' => 'Soustředění',
                        'volunteer' => 'Brigáda',
                        default => 'Ostatní',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'social' => 'success',
                        'meeting' => 'info',
                        'camp' => 'warning',
                        'volunteer' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('teams.name')
                    ->label('Týmy')
                    ->placeholder('Celý klub')
                    ->badge()
                    ->state(fn ($record) => $record->teams->reject(fn($team) => $team->category === 'all')->pluck('name'))
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->label('Od')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label('Veřejné')
                    ->boolean(),
                IconColumn::make('rsvp_enabled')
                    ->label('RSVP')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Typ akce')
                    ->options([
                        'social' => 'Společenská akce',
                        'meeting' => 'Schůzka / Porada',
                        'camp' => 'Soustředění / Kemp',
                        'volunteer' => 'Dobrovolnická akce / Brigáda',
                        'other' => 'Ostatní',
                    ]),
                SelectFilter::make('teams')
                    ->label('Tým')
                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('is_public')
                    ->label('Veřejnost'),
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

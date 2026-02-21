<?php

namespace App\Filament\Resources\PlayerProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class PlayerProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Jméno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jersey_number')
                    ->label('Dres #')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Pozice')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('teams.name')
                    ->label('Týmy')
                    ->badge()
                    ->separator(','),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('teams')
                    ->label('Tým')
                    ->relationship('teams', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Aktivní status'),
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

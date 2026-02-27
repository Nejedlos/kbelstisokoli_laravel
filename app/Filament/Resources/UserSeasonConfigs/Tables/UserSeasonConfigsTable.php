<?php

namespace App\Filament\Resources\UserSeasonConfigs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserSeasonConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Uživatel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label('Sezóna')
                    ->sortable(),
                TextColumn::make('tariff.name')
                    ->label('Tarif')
                    ->sortable(),
                TextColumn::make('opening_balance')
                    ->label('Zůstatek')
                    ->money('CZK')
                    ->sortable(),
                IconColumn::make('track_attendance')
                    ->label('Docházka')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('season')
                    ->label('Sezóna')
                    ->relationship('season', 'name'),
                SelectFilter::make('tariff')
                    ->label('Tarif')
                    ->relationship('tariff', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

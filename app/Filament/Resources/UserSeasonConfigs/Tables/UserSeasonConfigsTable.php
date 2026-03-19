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
                    ->label(__('admin.resources.user_season_config.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label(__('admin.resources.user_season_config.fields.season'))
                    ->sortable(),
                TextColumn::make('tariff.name')
                    ->label(__('admin.resources.user_season_config.fields.tariff'))
                    ->sortable(),
                TextColumn::make('opening_balance')
                    ->label(__('admin.resources.user_season_config.fields.opening_balance_short'))
                    ->money('CZK')
                    ->sortable(),
                IconColumn::make('track_attendance')
                    ->label(__('admin.resources.user_season_config.fields.track_attendance_short'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.user_season_config.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('season')
                    ->label(__('admin.resources.user_season_config.fields.season'))
                    ->relationship('season', 'name'),
                SelectFilter::make('tariff')
                    ->label(__('admin.resources.user_season_config.fields.tariff'))
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

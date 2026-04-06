<?php

namespace App\Filament\Resources\FinancialTariffs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinancialTariffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.financial_tariff.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('base_amount')
                    ->label(__('admin.resources.financial_tariff.fields.base_amount'))
                    ->money('CZK')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label(__('admin.resources.financial_tariff.fields.unit'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'month' => __('admin.resources.financial_tariff.units.month'),
                        'season' => __('admin.resources.financial_tariff.units.season'),
                        'event' => __('admin.resources.financial_tariff.units.event'),
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.financial_tariff.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

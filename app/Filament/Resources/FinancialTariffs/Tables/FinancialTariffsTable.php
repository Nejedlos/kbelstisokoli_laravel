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
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('base_amount')
                    ->label('Základní částka')
                    ->money('CZK')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Jednotka')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'month' => 'Měsíc',
                        'season' => 'Sezóna',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

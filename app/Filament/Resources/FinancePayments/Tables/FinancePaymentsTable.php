<?php

namespace App\Filament\Resources\FinancePayments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinancePaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paid_at')
                    ->label('Datum přijetí')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Plátce')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Neznámý plátce'),
                TextColumn::make('amount')
                    ->label('Částka')
                    ->money('CZK')
                    ->sortable(),
                TextColumn::make('allocated')
                    ->label('Alokováno')
                    ->state(fn ($record) => $record->amount_allocated)
                    ->money('CZK')
                    ->color(fn ($state, $record) => $state >= $record->amount ? 'success' : ($state > 0 ? 'warning' : 'gray')),
                TextColumn::make('variable_symbol')
                    ->label('VS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metoda')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'Převod',
                        'cash' => 'Hotovost',
                        'other' => 'Jiné',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'recorded' => 'info',
                        'confirmed' => 'success',
                        'reversed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'recorded' => 'Zapsáno',
                        'confirmed' => 'Potvrzeno',
                        'reversed' => 'Stornováno',
                        default => $state,
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'recorded' => 'Zapsáno',
                        'confirmed' => 'Potvrzeno',
                        'reversed' => 'Stornováno',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metoda')
                    ->options([
                        'bank_transfer' => 'Bankovní převod',
                        'cash' => 'Hotovost',
                        'other' => 'Jiné',
                    ]),
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

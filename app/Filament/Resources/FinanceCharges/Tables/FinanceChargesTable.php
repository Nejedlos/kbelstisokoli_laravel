<?php

namespace App\Filament\Resources\FinanceCharges\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinanceChargesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Člen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Položka / Účel')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->charge_type),
                TextColumn::make('amount_total')
                    ->label('Částka')
                    ->money('CZK')
                    ->sortable(),
                TextColumn::make('paid')
                    ->label('Zaplaceno')
                    ->state(fn ($record) => $record->paid_sum ?? 0)
                    ->money('CZK')
                    ->color(fn ($state, $record) => $state >= $record->amount_total ? 'success' : ($state > 0 ? 'warning' : 'gray')),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state, $record): string => match ($state) {
                        'draft' => 'gray',
                        'open' => ($record->due_date?->isPast() && ($record->amount_total - ($record->paid_sum ?? 0)) > 0) ? 'danger' : 'info',
                        'partially_paid' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'overdue' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state, $record): string => match ($state) {
                        'draft' => 'Koncept',
                        'open' => ($record->due_date?->isPast() && ($record->amount_total - ($record->paid_sum ?? 0)) > 0) ? 'Po splatnosti' : 'K úhradě',
                        'partially_paid' => 'Částečně',
                        'paid' => 'Zaplaceno',
                        'cancelled' => 'Zrušeno',
                        'overdue' => 'Po splatnosti',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Splatnost')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn ($record) => ($record->due_date?->isPast() && ($record->status !== 'paid' && $record->status !== 'cancelled') && ($record->amount_total - ($record->paid_sum ?? 0)) > 0) ? 'danger' : null),
                IconColumn::make('is_visible_to_member')
                    ->label('Viditelné')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'open' => 'K úhradě',
                        'partially_paid' => 'Částečně zaplaceno',
                        'paid' => 'Zaplaceno',
                        'overdue' => 'Po splatnosti',
                        'cancelled' => 'Zrušeno',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('charge_type')
                    ->label('Typ')
                    ->options([
                        'membership_fee' => 'Členský příspěvek',
                        'camp_fee' => 'Soustředění',
                        'tournament_fee' => 'Turnaj',
                        'other' => 'Ostatní',
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

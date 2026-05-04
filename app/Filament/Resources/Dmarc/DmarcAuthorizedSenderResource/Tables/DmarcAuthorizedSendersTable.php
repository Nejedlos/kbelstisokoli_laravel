<?php

namespace App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DmarcAuthorizedSendersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domain_name')
                    ->label('Doména')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sender_type')
                    ->label('Typ')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean(),
                TextColumn::make('last_seen_at')
                    ->label('Naposledy viděn')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
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

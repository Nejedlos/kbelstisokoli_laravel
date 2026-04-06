<?php

namespace App\Filament\Resources\LegacyImportBatches\Tables;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LegacyImportBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Název dávky')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued' => 'gray',
                        'running' => 'info',
                        'success' => 'success',
                        'partial_failed' => 'warning',
                        'failed' => 'danger',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('total_files')
                    ->label('Soubory')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('processed_files')
                    ->label('Zpracováno')
                    ->numeric()
                    ->description(fn ($record) => $record->success_files.' OK / '.$record->failed_files.' Chyba'),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('cancel')
                    ->label('Zrušit')
                    ->icon(FilamentIcon::get(AppIcon::DEACTIVATE))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'running' || $record->status === 'queued')
                    ->action(function ($record) {
                        $record->cancel();
                        Notification::make()
                            ->title('Import byl zrušen.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

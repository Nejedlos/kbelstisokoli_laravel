<?php

namespace App\Filament\Resources\Dmarc\DmarcMailboxResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\Dmarc\DmarcImapService;
use Filament\Notifications\Notification;
use App\Support\IconHelper;
use Illuminate\Support\HtmlString;

class MailboxesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('host')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'disabled' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'disabled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                TextColumn::make('last_checked_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_error')
                    ->limit(50)
                    ->color('danger'),
            ])
            ->actions([
                Action::make('ingest')
                    ->label('Importovat reporty')
                    ->icon(new HtmlString(IconHelper::render(IconHelper::REFRESH)))
                    ->action(function ($record, DmarcImapService $service) {
                        $run = $service->ingest($record);

                        if ($run->errors_count > 0) {
                            Notification::make()
                                ->title('Import dokončen s chybami')
                                ->body("Zpracováno {$run->reports_processed} reportů. Chyby: {$run->errors_count}")
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import úspěšný')
                                ->body("Zpracováno {$run->reports_processed} reportů.")
                                ->success()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ]);
    }
}

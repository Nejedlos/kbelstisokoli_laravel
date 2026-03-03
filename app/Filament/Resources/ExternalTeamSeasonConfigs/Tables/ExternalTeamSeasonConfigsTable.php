<?php

namespace App\Filament\Resources\ExternalTeamSeasonConfigs\Tables;

use App\Services\Stats\Sync\ExternalStatsSyncService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ExternalTeamSeasonConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('team.name')
                    ->label('Tým')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label('Sezóna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('external_season_year')
                    ->label('Rok')
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('last_synced_at')
                    ->label('Poslední sync')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('health')
                    ->label('Zdraví')
                    ->getStateUsing(fn ($record) => $record->getFailCountInARow())
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === 0 => 'success',
                        $state < 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state === 0 ? 'OK' : "$state chyby v řadě"),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('sync')
                    ->label('Sync')
                    ->icon('fas-arrows-rotate')
                    ->color('success')
                    ->action(fn ($record, ExternalStatsSyncService $service) => self::runSync($record, $service)),
                Action::make('dryRun')
                    ->label('Dry-run')
                    ->icon('fas-eye')
                    ->color('info')
                    ->modalHeading('Náhled synchronizace (Dry-run)')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Zavřít')
                    ->modalWidth('7xl')
                    ->infolist(fn ($record, ExternalStatsSyncService $service) => self::getDryRunInfolist($record, $service)),
                Action::make('forceSync')
                    ->label('Force Sync')
                    ->icon('fas-bolt')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record, ExternalStatsSyncService $service) => self::runSync($record, $service, ['force' => true])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function runSync($record, ExternalStatsSyncService $service, array $options = []): void
    {
        try {
            $service->syncTeamSeason($record->team_id, $record->season_id, $options);

            Notification::make()
                ->title('Synchronizace byla spuštěna')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Chyba při synchronizaci')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected static function getDryRunInfolist($record, ExternalStatsSyncService $service): Infolist
    {
        $data = $service->previewSync($record->team_id, $record->season_id);

        return Infolist::make()
            ->schema([
                RepeatableEntry::make('roster')
                    ->label('Extrahovaná soupiska')
                    ->getStateUsing(fn () => array_map(fn($row) => [
                        'name' => $row->values['player_name'] ?? $row->rowLabel,
                        'ext_id' => $row->playerId,
                        'birth' => $row->values['birth_year'] ?? '-',
                    ], $data['roster']->rows))
                    ->schema([
                        TextEntry::make('name')->label('Jméno'),
                        TextEntry::make('ext_id')->label('Externí ID'),
                        TextEntry::make('birth')->label('Ročník'),
                    ])
                    ->columns(3),
                RepeatableEntry::make('matches')
                    ->label('Extrahované zápasy')
                    ->getStateUsing(fn () => array_map(fn($row) => [
                        'date' => $row->values['scheduled_at'] ?? '-',
                        'opponent' => $row->values['opponent'] ?? '-',
                        'score' => $row->values['score'] ?? '-',
                        'ext_id' => $row->values['match_external_id'] ?? '-',
                    ], $data['matches']->rows))
                    ->schema([
                        TextEntry::make('date')->label('Datum'),
                        TextEntry::make('opponent')->label('Soupeř'),
                        TextEntry::make('score')->label('Skóre'),
                        TextEntry::make('ext_id')->label('Externí ID'),
                    ])
                    ->columns(4),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ExternalEntityMappings\Tables;

use App\Models\User;
use App\Services\Stats\Sync\StatisticSyncService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExternalEntityMappingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('season.name')
                    ->label('Sezóna')
                    ->sortable(),
                TextColumn::make('external_id')
                    ->label('Externí ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('metadata.player_name')
                    ->label('Jméno (externí)')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('metadata.birth_year')
                    ->label('Ročník')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Přiřazený uživatel')
                    ->placeholder('Nespárováno')
                    ->color(fn ($record) => $record->internal_id ? 'success' : 'danger')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_matched')
                    ->label('Stav párování')
                    ->placeholder('Vše')
                    ->trueLabel('Spárovaní')
                    ->falseLabel('Nespárovaní')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('internal_id'),
                        false: fn ($query) => $query->whereNull('internal_id'),
                    ),
                SelectFilter::make('season_id')
                    ->label('Sezóna')
                    ->relationship('season', 'name'),
            ])
            ->actions([
                Action::make('linkUser')
                    ->label('Spárovat')
                    ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-link"></i>'))
                    ->color('primary')
                    ->form([
                        Select::make('user_id')
                            ->label('Interní uživatel')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function ($record, array $data, StatisticSyncService $service) {
                        $service->linkPlayerAndRecompute($record, $data['user_id']);

                        Notification::make()
                            ->title('Hráč byl spárován a statistiky přepočteny')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => ! $record->internal_id),
                Action::make('recompute')
                    ->label('Přepočítat')
                    ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-arrows-rotate"></i>'))
                    ->color('info')
                    ->action(function ($record, StatisticSyncService $service) {
                        if ($record->internal_id) {
                            $service->linkPlayerAndRecompute($record, $record->internal_id);
                            Notification::make()
                                ->title('Statistiky byly přepočteny')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => (bool) $record->internal_id),
                EditAction::make()
                    ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-pen-to-square"></i>')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('batchLinkUser')
                        ->label('Hromadně spárovat')
                        ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-users"></i>'))
                        ->form([
                            Select::make('user_id')
                                ->label('Přiřadit k uživateli')
                                ->options(User::all()->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Support\Collection $records, array $data, StatisticSyncService $service) {
                            $records->each(function ($record) use ($data, $service) {
                                $service->linkPlayerAndRecompute($record, $data['user_id']);
                            });

                            Notification::make()
                                ->title('Hráči byli hromadně spárováni')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('batchRecompute')
                        ->label('Hromadně přepočítat')
                        ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-arrows-rotate"></i>'))
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records, StatisticSyncService $service) {
                            $records->each(function ($record) use ($service) {
                                if ($record->internal_id) {
                                    $service->linkPlayerAndRecompute($record, $record->internal_id);
                                }
                            });

                            Notification::make()
                                ->title('Statistiky byly hromadně přepočteny')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-trash"></i>')),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ExternalEntityMappings\Tables;

use App\Models\User;
use App\Models\ExternalEntityMapping;
use App\Services\Stats\Sync\StatisticSyncService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                ViewColumn::make('proposal')
                    ->label('Návrh párování')
                    ->view('filament.resources.external-entity-mappings.columns.proposal-column'),
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
                        ->icon(new HtmlString('<i class="fa-light fa-arrows-rotate"></i>'))
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
                    BulkAction::make('autoPair')
                        ->label('Automaticky spárovat')
                        ->icon(new HtmlString('<i class="fa-light fa-magic"></i>'))
                        ->color('success')
                        ->action(function (\Illuminate\Support\Collection $records, StatisticSyncService $service) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->internal_id) {
                                    continue;
                                }

                                $proposal = $this->getMappingProposal($record);
                                if ($proposal['user'] && ! $proposal['is_duplicate']) {
                                    $service->linkPlayerAndRecompute($record, $proposal['user']->id);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("Automaticky spárováno {$count} hráčů")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-trash"></i>')),
                ]),
            ]);
    }

    public static function getMappingProposal($record): array
    {
        $externalName = $record->metadata['player_name'] ?? null;
        if (! $externalName) {
            return ['user' => null, 'is_ghost' => false, 'is_duplicate' => false];
        }

        $parts = explode(' ', trim($externalName));
        if (count($parts) < 2) {
            return ['user' => null, 'is_ghost' => false, 'is_duplicate' => false];
        }

        $p1 = $parts[0];
        $p2 = implode(' ', array_slice($parts, 1));

        // 1. Najít reálné uživatele (ne ghosty)
        $realUsers = User::where(function ($q) {
            $q->whereNull('email')
                ->orWhere('email', 'NOT LIKE', 'ghost_%');
        })
            ->where(function ($q) use ($externalName, $p1, $p2) {
                $q->where('name', $externalName)
                    ->orWhere('name', "{$p2} {$p1}")
                    ->orWhere(function ($q2) use ($p1, $p2) {
                        $q2->where('first_name', $p1)->where('last_name', $p2);
                    })
                    ->orWhere(function ($q2) use ($p1, $p2) {
                        $q2->where('first_name', $p2)->where('last_name', $p1);
                    });
            })->get();

        // 2. Najít ghost uživatele se stejným jménem
        $ghostUsers = User::where('email', 'LIKE', 'ghost_%')
            ->where(function ($q) use ($externalName, $p1, $p2) {
                $q->where('name', $externalName)
                    ->orWhere('name', "{$p2} {$p1}")
                    ->orWhere(function ($q2) use ($p1, $p2) {
                        $q2->where('first_name', $p1)->where('last_name', $p2);
                    })
                    ->orWhere(function ($q2) use ($p1, $p2) {
                        $q2->where('first_name', $p2)->where('last_name', $p1);
                    });
            })->get();

        $isDuplicate = $realUsers->count() > 0 && $ghostUsers->count() > 0;

        if ($realUsers->count() === 1) {
            return ['user' => $realUsers->first(), 'is_ghost' => false, 'is_duplicate' => $isDuplicate];
        }

        if ($ghostUsers->count() === 1) {
            return ['user' => $ghostUsers->first(), 'is_ghost' => true, 'is_duplicate' => $isDuplicate];
        }

        return ['user' => null, 'is_ghost' => false, 'is_duplicate' => $isDuplicate];
    }
}

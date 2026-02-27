<?php

namespace App\Filament\Resources\Seasons\Tables;

use App\Models\Season;
use App\Models\UserSeasonConfig;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeasonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('initialize_configs')
                    ->label('Inicializovat konfigurace')
                    ->icon(IconHelper::get(IconHelper::COPY))
                    ->form([
                        Select::make('source_season_id')
                            ->label('Zdrojová sezóna')
                            ->options(fn (?Season $record) => $record ? Season::where('id', '!=', $record->id)->pluck('name', 'id') : [])
                            ->required(),
                    ])
                    ->action(function (Season $record, array $data) {
                        $sourceSeasonId = $data['source_season_id'];
                        $configs = UserSeasonConfig::where('season_id', $sourceSeasonId)->get();

                        $count = 0;
                        foreach ($configs as $config) {
                            UserSeasonConfig::updateOrCreate(
                                [
                                    'user_id' => $config->user_id,
                                    'season_id' => $record->id,
                                ],
                                [
                                    'financial_tariff_id' => $config->financial_tariff_id,
                                    'billing_start_month' => $config->billing_start_month,
                                    'billing_end_month' => $config->billing_end_month,
                                    'exemption_start_month' => $config->exemption_start_month,
                                    'exemption_end_month' => $config->exemption_end_month,
                                    'track_attendance' => $config->track_attendance,
                                    'opening_balance' => 0, // V budoucnu výpočet salda
                                ]
                            );
                            $count++;
                        }

                        Notification::make()
                            ->title("Inicializace dokončena")
                            ->body("Bylo vytvořeno/aktualizováno {$count} konfigurací pro sezónu {$record->name}.")
                            ->success()
                            ->send();
                    })
                    ->color('warning'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

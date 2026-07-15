<?php

namespace App\Filament\Resources\Trainings\Tables;

use App\Models\Training;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use App\Support\TrainingRecurringHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->recordClasses(fn (Training $record) => $record->starts_at->isFuture() ? 'bg-success-50/70 dark:bg-success-900/10' : 'bg-gray-50/50 dark:bg-white/5')
            ->columns([
                IconColumn::make('sport')
                    ->label(__('admin.resources.training.fields.sport'))
                    ->icon(fn (string $state): string|HtmlString => match ($state) {
                        'volleyball' => FilamentIcon::get(AppIcon::VOLLEYBALL),
                        default => FilamentIcon::get(AppIcon::MATCHES),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'volleyball' => 'info',
                        default => 'primary',
                    })
                    ->alignCenter(),
                TextColumn::make('teams.name')
                    ->label(__('admin.resources.team.plural_label'))
                    ->badge()
                    ->state(fn (Training $record) => $record->teams->reject(fn ($team) => $team->category === 'all')->pluck('name'))
                    ->searchable(),
                TextColumn::make('mismatches_count')
                    ->label(__('fields.mismatches'))
                    ->counts('mismatches')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('location')
                    ->label(__('fields.location'))
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->label(__('fields.starts_at'))
                    ->dateTime('j.n.Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('fields.ends_at'))
                    ->dateTime('j.n.Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.training.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.training.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                Action::make('replicate')
                    ->label(__('admin.resources.training.bulk_actions.replicate.label'))
                    ->icon(FilamentIcon::get(AppIcon::COPY))
                    ->color('gray')
                    ->form([
                        Select::make('replicate_mode')
                            ->label(__('admin.resources.training.bulk_actions.replicate.fields.mode'))
                            ->options([
                                'single' => __('admin.resources.training.bulk_actions.replicate.fields.mode_single'),
                                'recurring' => __('admin.resources.training.bulk_actions.replicate.fields.mode_recurring'),
                            ])
                            ->default('single')
                            ->live(),
                        DateTimePicker::make('target_date')
                            ->label(__('admin.resources.training.bulk_actions.replicate.fields.target_date'))
                            ->native(false)
                            ->required()
                            ->visible(fn ($get) => $get('replicate_mode') === 'single'),
                        Select::make('repeat_frequency')
                            ->label(__('admin.resources.training.fields.recurring.frequency'))
                            ->options([
                                'daily' => __('admin.resources.training.fields.recurring.frequency_daily'),
                                'weekly' => __('admin.resources.training.fields.recurring.frequency_weekly'),
                                'monthly' => __('admin.resources.training.fields.recurring.frequency_monthly'),
                            ])
                            ->required()
                            ->visible(fn ($get) => $get('replicate_mode') === 'recurring'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('repeat_count')
                                    ->label(__('admin.resources.training.fields.recurring.count'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(50),
                                Select::make('repeat_period')
                                    ->label(__('admin.resources.training.fields.recurring.period'))
                                    ->options([
                                        '1_month' => __('admin.resources.training.fields.recurring.period_1_month'),
                                        '2_months' => __('admin.resources.training.fields.recurring.period_2_months'),
                                        '3_months' => __('admin.resources.training.fields.recurring.period_3_months'),
                                        '6_months' => __('admin.resources.training.fields.recurring.period_6_months'),
                                        'this_season' => __('admin.resources.training.fields.recurring.period_this_season'),
                                    ]),
                            ])
                            ->visible(fn ($get) => $get('replicate_mode') === 'recurring'),
                    ])
                    ->action(function (Training $record, array $data): void {
                        TrainingRecurringHelper::replicate($record, $data);

                        Notification::make()
                            ->title(__('admin.resources.training.bulk_actions.replicate.success_notification'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('replicate')
                        ->label(__('admin.resources.training.bulk_actions.replicate.label'))
                        ->icon(FilamentIcon::get(AppIcon::COPY))
                        ->form([
                            Select::make('replicate_mode')
                                ->label(__('admin.resources.training.bulk_actions.replicate.fields.mode'))
                                ->options([
                                    'single' => __('admin.resources.training.bulk_actions.replicate.fields.mode_single'),
                                    'recurring' => __('admin.resources.training.bulk_actions.replicate.fields.mode_recurring'),
                                ])
                                ->default('single')
                                ->live(),
                            DateTimePicker::make('target_date')
                                ->label(__('admin.resources.training.bulk_actions.replicate.fields.target_date'))
                                ->native(false)
                                ->required()
                                ->visible(fn ($get) => $get('replicate_mode') === 'single'),
                            Select::make('repeat_frequency')
                                ->label(__('admin.resources.training.fields.recurring.frequency'))
                                ->options([
                                    'daily' => __('admin.resources.training.fields.recurring.frequency_daily'),
                                    'weekly' => __('admin.resources.training.fields.recurring.frequency_weekly'),
                                    'monthly' => __('admin.resources.training.fields.recurring.frequency_monthly'),
                                ])
                                ->required()
                                ->visible(fn ($get) => $get('replicate_mode') === 'recurring'),
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('repeat_count')
                                        ->label(__('admin.resources.training.fields.recurring.count'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(50),
                                    Select::make('repeat_period')
                                        ->label(__('admin.resources.training.fields.recurring.period'))
                                        ->options([
                                            '1_month' => __('admin.resources.training.fields.recurring.period_1_month'),
                                            '2_months' => __('admin.resources.training.fields.recurring.period_2_months'),
                                            '3_months' => __('admin.resources.training.fields.recurring.period_3_months'),
                                            '6_months' => __('admin.resources.training.fields.recurring.period_6_months'),
                                            'this_season' => __('admin.resources.training.fields.recurring.period_this_season'),
                                        ]),
                                ])
                                ->visible(fn ($get) => $get('replicate_mode') === 'recurring'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (Training $record) => TrainingRecurringHelper::replicate($record, $data));

                            Notification::make()
                                ->title(__('admin.resources.training.bulk_actions.replicate.success_notification'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('change_location')
                        ->label(__('admin.resources.training.bulk_actions.change_location.label'))
                        ->icon(FilamentIcon::get(AppIcon::LOCATION))
                        ->modalDescription(__('admin.resources.training.bulk_actions.change_location.modal_description'))
                        ->form([
                            TextInput::make('new_location')
                                ->label(__('admin.resources.training.fields.location'))
                                ->placeholder(__('admin.resources.training.fields.location_placeholder'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (Training $record) => $record->update([
                                'location' => $data['new_location'],
                            ]));

                            Notification::make()
                                ->title(__('admin.resources.training.bulk_actions.change_location.success_notification'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('change_teams')
                        ->label(__('admin.resources.training.bulk_actions.change_teams.label'))
                        ->icon(FilamentIcon::get(AppIcon::TEAMS))
                        ->modalDescription(__('admin.resources.training.bulk_actions.change_teams.modal_description'))
                        ->form([
                            Select::make('teams')
                                ->label(__('admin.resources.training.fields.teams'))
                                ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (Training $record) => $record->teams()->sync($data['teams']));

                            Notification::make()
                                ->title(__('admin.resources.training.bulk_actions.change_teams.success_notification'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('change_sport')
                        ->label(__('admin.resources.training.bulk_actions.change_sport.label'))
                        ->icon(FilamentIcon::get(AppIcon::MATCHES))
                        ->modalDescription(__('admin.resources.training.bulk_actions.change_sport.modal_description'))
                        ->form([
                            Select::make('sport')
                                ->label(__('admin.resources.training.fields.sport'))
                                ->options([
                                    'basketball' => __('admin.resources.training.fields.sport_basketball'),
                                    'volleyball' => __('admin.resources.training.fields.sport_volleyball'),
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (Training $record) => $record->update([
                                'sport' => $data['sport'],
                            ]));

                            Notification::make()
                                ->title(__('admin.resources.training.bulk_actions.change_sport.success_notification'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}

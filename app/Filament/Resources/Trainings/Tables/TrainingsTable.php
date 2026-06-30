<?php

namespace App\Filament\Resources\Trainings\Tables;

use App\Models\Training;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->recordClasses(fn (Training $record) => $record->starts_at->isFuture() ? 'bg-success-50/70 dark:bg-success-900/10' : 'bg-gray-50/50 dark:bg-white/5')
            ->columns([
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
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
                ]),
            ]);
    }
}

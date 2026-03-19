<?php

namespace App\Filament\Resources\OpponentMergeSuggestions\Tables;

use App\Models\OpponentMergeSuggestion;
use App\Services\Stats\OpponentMergeService;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;

class OpponentMergeSuggestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sourceOpponent.name')
                    ->label(__('admin.resources.opponent_merge_suggestion.fields.source_opponent'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('targetOpponent.name')
                    ->label(__('admin.resources.opponent_merge_suggestion.fields.target_opponent'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity')
                    ->label(__('admin.resources.opponent_merge_suggestion.fields.similarity'))
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin.resources.opponent_merge_suggestion.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __('admin.resources.opponent_merge_suggestion.status.' . $state)),
                TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('similarity', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.resources.opponent_merge_suggestion.fields.status'))
                    ->options([
                        'pending' => __('admin.resources.opponent_merge_suggestion.status.pending'),
                        'accepted' => __('admin.resources.opponent_merge_suggestion.status.accepted'),
                        'rejected' => __('admin.resources.opponent_merge_suggestion.status.rejected'),
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label(__('admin.resources.opponent_merge_suggestion.actions.accept'))
                    ->icon(FilamentIcon::get(AppIcon::ACTIVATE))
                    ->color('success')
                    ->hidden(fn (OpponentMergeSuggestion $record) => $record->status !== 'pending')
                    ->form(fn (OpponentMergeSuggestion $record) => [
                        TextInput::make('new_name')
                            ->label(__('admin.resources.opponent_merge_suggestion.fields.new_name'))
                            ->default($record->targetOpponent->name)
                            ->required()
                            ->hintActions([
                                Action::make('use_target')
                                    ->label(__('admin.resources.opponent_merge_suggestion.actions.use_target'))
                                    ->icon(FilamentIcon::get(AppIcon::REFRESH))
                                    ->action(fn ($set) => $set('new_name', $record->targetOpponent->name)),
                                Action::make('use_source')
                                    ->label(__('admin.resources.opponent_merge_suggestion.actions.use_source'))
                                    ->icon(FilamentIcon::get(AppIcon::REFRESH))
                                    ->action(fn ($set) => $set('new_name', $record->sourceOpponent->name)),
                            ]),
                    ])
                    ->action(function (OpponentMergeSuggestion $record, array $data, OpponentMergeService $service) {
                        $success = $service->merge($record, $data['new_name']);

                        if ($success) {
                            Notification::make()
                                ->title(__('admin.resources.opponent_merge_suggestion.notifications.merge_success'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin.resources.opponent_merge_suggestion.notifications.merge_error'))
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->label(__('admin.resources.opponent_merge_suggestion.actions.reject'))
                    ->icon(FilamentIcon::get(AppIcon::CANCEL))
                    ->color('danger')
                    ->hidden(fn (OpponentMergeSuggestion $record) => $record->status !== 'pending')
                    ->action(function (OpponentMergeSuggestion $record, OpponentMergeService $service) {
                        $service->reject($record);

                        Notification::make()
                            ->title(__('admin.resources.opponent_merge_suggestion.notifications.rejected'))
                            ->info()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

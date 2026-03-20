<?php

namespace App\Filament\Resources\BasketballMatches\Tables;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class BasketballMatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('scheduled_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('scheduled_at')
                    ->label(__('admin.resources.basketball_match.fields.scheduled_at'))
                    ->formatStateUsing(fn ($state, $record) => new HtmlString(
                        ((! empty($record->metadata['external_id']) || ! empty($record->metadata['season_external_match_id']))
                            ? '<i class="fa-light fa-cloud-arrow-down fa-fw text-info mr-1" title="' . __('admin.resources.basketball_match.tooltips.external_sync') . '"></i> '
                            : '').$state->format('d.m.Y H:i')
                    ))
                    ->sortable(),
                TextColumn::make('teams.name')
                    ->label(__('admin.resources.basketball_match.fields.teams'))
                    ->badge()
                    ->searchable(query: function ($query, string $search): \Illuminate\Database\Eloquent\Builder {
                        $locale = app()->getLocale();
                        return $query->whereHas('teams', function ($q) use ($search, $locale) {
                            $q->where("name->{$locale}", 'LIKE', "%{$search}%");
                        });
                    }),
                TextColumn::make('opponent.name')
                    ->label(__('admin.resources.basketball_match.fields.opponent'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mismatches_count')
                    ->label(__('admin.resources.basketball_match.fields.mismatches'))
                    ->counts('mismatches')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('score')
                    ->label(__('admin.resources.basketball_match.fields.score'))
                    ->state(fn ($record) => in_array($record->status, ['finished', 'completed', 'played']) ? \App\Support\MatchResultHelper::formatScore("{$record->score_home}:{$record->score_away}") : '-')
                    ->badge()
                    ->color(fn ($record): string => match (true) {
                        ! in_array($record->status, ['finished', 'completed', 'played']) => 'gray',
                        $record->is_draw => 'gray',
                        $record->is_win => 'success',
                        $record->is_loss => 'danger',
                        default => 'gray',
                    })
                    ->description(fn ($record): ?string => match (true) {
                        ! in_array($record->status, ['finished', 'completed', 'played']) => null,
                        $record->is_draw => __('matches.draw'),
                        $record->is_win => __('matches.victory'),
                        $record->is_loss => __('matches.loss'),
                        default => null,
                    }),
                TextColumn::make('status')
                    ->label(__('admin.resources.basketball_match.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planned' => 'info',
                        'scheduled' => 'info',
                        'played' => 'success',
                        'completed' => 'success',
                        'finished' => 'success',
                        'cancelled' => 'danger',
                        'postponed' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => __('admin.resources.basketball_match.statuses.planned'),
                        'scheduled' => __('admin.resources.basketball_match.statuses.scheduled'),
                        'played' => __('admin.resources.basketball_match.statuses.played'),
                        'completed' => __('admin.resources.basketball_match.statuses.completed'),
                        'finished' => __('admin.resources.basketball_match.statuses.finished'),
                        'cancelled' => __('admin.resources.basketball_match.statuses.cancelled'),
                        'postponed' => __('admin.resources.basketball_match.statuses.postponed'),
                        default => $state,
                    }),
                IconColumn::make('is_home')
                    ->label(__('admin.resources.basketball_match.fields.is_home'))
                    ->boolean(),
                TextColumn::make('location')
                    ->label(__('admin.resources.basketball_match.fields.location'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('teams')
                    ->label(__('admin.resources.basketball_match.fields.teams'))
                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('season')
                    ->label(__('admin.resources.basketball_match.fields.season'))
                    ->relationship('season', 'name'),
                SelectFilter::make('status')
                    ->label(__('admin.resources.basketball_match.fields.status'))
                    ->options([
                        'planned' => __('admin.resources.basketball_match.statuses.planned'),
                        'scheduled' => __('admin.resources.basketball_match.statuses.scheduled'),
                        'finished' => __('admin.resources.basketball_match.statuses.finished'),
                        'played' => __('admin.resources.basketball_match.statuses.played'),
                        'completed' => __('admin.resources.basketball_match.statuses.completed'),
                        'cancelled' => __('admin.resources.basketball_match.statuses.cancelled'),
                        'postponed' => __('admin.resources.basketball_match.statuses.postponed'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('ai_sync')
                        ->label(__('admin.resources.basketball_match.actions.ai_sync'))
                        ->icon(FilamentIcon::get(AppIcon::AI))
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $records->each(function ($record) {
                                \App\Jobs\Stats\SyncMatchDetailJob::dispatch($record->id, [
                                    'force' => true,
                                    'fresh' => true,
                                    'ai' => true,
                                ]);
                            });
                            Notification::make()->title(__('admin.resources.basketball_match.notifications.ai_sync_scheduled'))->success()->send();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}

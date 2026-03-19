<?php

namespace App\Filament\Resources\ExternalImportRuns\Tables;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExternalImportRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.resources.external_import_run.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('run_type')
                    ->label(__('admin.resources.external_import_run.fields.run_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'team_page' => __('admin.resources.external_import_run.run_types.team_page'),
                        'matches_list' => __('admin.resources.external_import_run.run_types.matches_list'),
                        'match_detail' => __('admin.resources.external_import_run.run_types.match_detail'),
                        'preview' => __('admin.resources.external_import_run.run_types.preview'),
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin.resources.external_import_run.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'success' => __('admin.resources.external_import_run.statuses.success'),
                        'skipped' => __('admin.resources.external_import_run.statuses.skipped'),
                        'failed' => __('admin.resources.external_import_run.statuses.failed'),
                        'partial_failed' => __('admin.resources.external_import_run.statuses.partial_failed'),
                        'running' => __('admin.resources.external_import_run.statuses.running'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'skipped' => 'gray',
                        'failed' => 'danger',
                        'partial_failed' => 'warning',
                        'running' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label(__('admin.resources.external_import_run.fields.team'))
                    ->placeholder('N/A')
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label(__('admin.resources.external_import_run.fields.season'))
                    ->sortable(),
                TextColumn::make('imported_count')
                    ->label(__('admin.resources.external_import_run.fields.imported_count_short'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('metadata.used_ai_fallback')
                    ->label(__('admin.resources.external_import_run.fields.ai'))
                    ->boolean()
                    ->trueIcon(FilamentIcon::get(AppIcon::AI))
                    ->trueColor('warning')
                    ->falseIcon('')
                    ->toggleable(),
                TextColumn::make('finished_at')
                    ->label(__('admin.resources.external_import_run.fields.duration'))
                    ->getStateUsing(fn ($record) => $record->finished_at ? $record->started_at->diffForHumans($record->finished_at, true) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('run_type')
                    ->label(__('admin.resources.external_import_run.fields.run_type'))
                    ->options([
                        'team_page' => __('admin.resources.external_import_run.run_types.team_page'),
                        'matches_list' => __('admin.resources.external_import_run.run_types.matches_list'),
                        'match_detail' => __('admin.resources.external_import_run.run_types.match_detail'),
                        'preview' => __('admin.resources.external_import_run.run_types.preview'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('admin.resources.external_import_run.fields.status'))
                    ->options([
                        'success' => __('admin.resources.external_import_run.statuses.success'),
                        'skipped' => __('admin.resources.external_import_run.statuses.skipped'),
                        'failed' => __('admin.resources.external_import_run.statuses.failed'),
                        'partial_failed' => __('admin.resources.external_import_run.statuses.partial_failed'),
                        'running' => __('admin.resources.external_import_run.statuses.running'),
                    ]),
                SelectFilter::make('team_id')
                    ->label(__('admin.resources.external_import_run.fields.team'))
                    ->relationship('team', 'name'),
                SelectFilter::make('season_id')
                    ->label(__('admin.resources.external_import_run.fields.season'))
                    ->relationship('season', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => "/admin/external-import-runs/{$record->id}"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

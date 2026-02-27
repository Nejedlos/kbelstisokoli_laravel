<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Support\IconHelper;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconSize;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->label(__('admin.resources.audit_log.fields.occurred_at'))
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('category')
                    ->label(__('admin.resources.audit_log.fields.category'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.categories.$state") ?? $state)
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('event_key')
                    ->label(__('admin.resources.audit_log.fields.event_key'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('action')
                    ->label(__('admin.resources.audit_log.fields.action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'login' => 'info',
                        'failed_login' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'created' => IconHelper::get('plus'),
                        'updated' => IconHelper::get('pen-to-square'),
                        'deleted' => IconHelper::get('trash'),
                        'login' => IconHelper::get('right-to-bracket'),
                        'logout' => IconHelper::get('right-from-bracket'),
                        'password_reset' => IconHelper::get('key'),
                        'failed_login' => IconHelper::get('circle-exclamation'),
                        default => IconHelper::get('circle-info'),
                    })
                    ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.actions.$state") ?? $state)
                    ->sortable(),
                TextColumn::make('actor.name')
                    ->label(__('admin.resources.audit_log.fields.actor'))
                    ->description(fn ($record) => $record->is_system_event ? 'System' : ($record->actor?->email ?? 'Guest'))
                    ->sortable(),
                TextColumn::make('subject_label')
                    ->label(__('admin.resources.audit_log.fields.subject'))
                    ->description(fn ($record) => $record->subject_type ? class_basename($record->subject_type) : null)
                    ->searchable(),
                TextColumn::make('changes')
                    ->label(__('admin.resources.audit_log.fields.changes'))
                    ->formatStateUsing(function ($state) {
                        if (empty($state['after'])) return null;
                        $keys = array_keys($state['after']);
                        $labels = array_map(fn($k) => __("fields.$k") !== "fields.$k" ? __("fields.$k") : $k, $keys);
                        return implode(', ', $labels);
                    })
                    ->color('gray')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('severity')
                    ->label(__('admin.resources.audit_log.fields.severity'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'warning' => 'warning',
                        'info' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.severities.$state") ?? $state)
                    ->sortable(),
                TextColumn::make('source')
                    ->label(__('admin.resources.audit_log.fields.source'))
                    ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.sources.$state") ?? $state)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label(__('admin.resources.audit_log.fields.category'))
                    ->options([
                        'auth' => __('admin.resources.audit_log.categories.auth'),
                        'admin_crud' => __('admin.resources.audit_log.categories.admin_crud'),
                        'lead' => __('admin.resources.audit_log.categories.lead'),
                        'settings' => __('admin.resources.audit_log.categories.settings'),
                        'content' => __('admin.resources.audit_log.categories.content'),
                        'system' => __('admin.resources.audit_log.categories.system'),
                    ]),
                SelectFilter::make('severity')
                    ->label(__('admin.resources.audit_log.fields.severity'))
                    ->options([
                        'info' => __('admin.resources.audit_log.severities.info'),
                        'warning' => __('admin.resources.audit_log.severities.warning'),
                        'critical' => __('admin.resources.audit_log.severities.critical'),
                    ]),
                Filter::make('occurred_at')
                    ->form([
                        DatePicker::make('from')->label(__('general.date_from') !== 'general.date_from' ? __('general.date_from') : 'Od'),
                        DatePicker::make('to')->label(__('general.date_to') !== 'general.date_to' ? __('general.date_to') : 'Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('occurred_at', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('occurred_at', '<=', $date),
                            );
                    }),
                SelectFilter::make('source')
                    ->label(__('admin.resources.audit_log.fields.source'))
                    ->options([
                        'web' => __('admin.resources.audit_log.sources.web'),
                        'admin' => __('admin.resources.audit_log.sources.admin'),
                        'console' => __('admin.resources.audit_log.sources.console'),
                        'api' => __('admin.resources.audit_log.sources.api'),
                        'job' => __('admin.resources.audit_log.sources.job'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}

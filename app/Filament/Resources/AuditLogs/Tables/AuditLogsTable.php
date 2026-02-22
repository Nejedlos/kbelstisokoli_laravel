<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\HtmlString;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Čas')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategorie')
                    ->badge()
                    ->sortable(),
                TextColumn::make('event_key')
                    ->label('Událost')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('actor.name')
                    ->label('Aktér')
                    ->description(fn ($record) => $record->is_system_event ? 'Systém' : ($record->actor?->email ?? 'Host'))
                    ->sortable(),
                TextColumn::make('subject_label')
                    ->label('Předmět')
                    ->description(fn ($record) => $record->subject_type ? class_basename($record->subject_type) : null)
                    ->searchable(),
                TextColumn::make('severity')
                    ->label('Závažnost')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'warning' => 'warning',
                        'info' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('source')
                    ->label('Zdroj')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategorie')
                    ->options([
                        'auth' => 'Auth',
                        'admin_crud' => 'Admin CRUD',
                        'lead' => 'Lead',
                        'settings' => 'Settings',
                        'content' => 'Content',
                        'system' => 'System',
                    ]),
                SelectFilter::make('severity')
                    ->label('Závažnost')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Varování',
                        'critical' => 'Kritické',
                    ]),
                Filter::make('occurred_at')
                    ->form([
                        DatePicker::make('from')->label('Od'),
                        DatePicker::make('to')->label('Do'),
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
                    ->label('Zdroj')
                    ->options([
                        'web' => 'Web',
                        'admin' => 'Admin',
                        'console' => 'Console',
                        'api' => 'API',
                        'job' => 'Job',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }
}

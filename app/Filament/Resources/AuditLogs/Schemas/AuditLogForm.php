<?php

namespace App\Filament\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.audit_log.tabs.general'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('occurred_at')
                            ->label(__('admin.resources.audit_log.fields.occurred_at'))
                            ->disabled(),
                        TextInput::make('category')
                            ->label(__('admin.resources.audit_log.fields.category'))
                            ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.categories.$state") ?? $state)
                            ->disabled(),
                        TextInput::make('event_key')
                            ->label(__('admin.resources.audit_log.fields.event_key'))
                            ->disabled(),
                        TextInput::make('action')
                            ->label(__('admin.resources.audit_log.fields.action'))
                            ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.actions.$state") ?? $state)
                            ->disabled(),
                        TextInput::make('severity')
                            ->label(__('admin.resources.audit_log.fields.severity'))
                            ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.severities.$state") ?? $state)
                            ->disabled(),
                        TextInput::make('source')
                            ->label(__('admin.resources.audit_log.fields.source'))
                            ->formatStateUsing(fn (string $state): string => __("admin.resources.audit_log.sources.$state") ?? $state)
                            ->disabled(),
                    ]),

                Section::make(__('admin.resources.audit_log.tabs.actor_subject'))
                    ->columns(2)
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('actor.name')
                                    ->label(__('admin.resources.audit_log.fields.actor'))
                                    ->disabled(),
                                TextInput::make('actor_type')
                                    ->label(__('admin.resources.audit_log.fields.actor') . ' (Type)')
                                    ->disabled(),
                            ]),
                        Grid::make()
                            ->schema([
                                TextInput::make('subject_label')
                                    ->label(__('admin.resources.audit_log.fields.subject'))
                                    ->disabled(),
                                TextInput::make('subject_type')
                                    ->label(__('admin.resources.audit_log.fields.subject') . ' (Type)')
                                    ->disabled(),
                                TextInput::make('subject_id')
                                    ->label(__('admin.resources.audit_log.fields.subject') . ' (ID)')
                                    ->disabled(),
                            ]),
                    ]),

                Section::make(__('admin.resources.audit_log.tabs.context'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('url')
                            ->label(__('admin.resources.audit_log.fields.url'))
                            ->disabled(),
                        TextInput::make('route_name')
                            ->label(__('admin.resources.audit_log.fields.url') . ' (Route)')
                            ->disabled(),
                        TextInput::make('ip_address')
                            ->label(__('admin.resources.audit_log.fields.ip_address'))
                            ->disabled(),
                        TextInput::make('request_id')
                            ->label('Request ID')
                            ->disabled(),
                        Textarea::make('user_agent_summary')
                            ->label(__('admin.resources.audit_log.fields.user_agent'))
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.resources.audit_log.fields.changes') . ' & ' . __('admin.resources.audit_log.fields.metadata'))
                    ->schema([
                        Textarea::make('changes')
                            ->label(__('admin.resources.audit_log.fields.changes'))
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                            ->rows(10),
                        Textarea::make('metadata')
                            ->label(__('admin.resources.audit_log.fields.metadata'))
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                            ->rows(5),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\NotFoundLogs\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotFoundLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.not_found_log.sections.error'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('url')
                                    ->label(__('admin.resources.not_found_log.fields.url'))
                                    ->readOnly(),
                                TextInput::make('hits_count')
                                    ->label(__('admin.resources.not_found_log.fields.hits_count'))
                                    ->numeric()
                                    ->readOnly(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Placeholder::make('last_seen_at')
                                    ->label(__('admin.resources.not_found_log.fields.last_seen_at'))
                                    ->content(fn ($record) => $record?->last_seen_at?->format('d.m.Y H:i:s') ?? '-'),
                                TextInput::make('status')
                                    ->label(__('admin.resources.not_found_log.fields.status'))
                                    ->readOnly(),
                            ]),
                    ]),

                Section::make(__('admin.resources.not_found_log.sections.request'))
                    ->schema([
                        TextInput::make('referer')
                            ->label(__('admin.resources.not_found_log.fields.referer'))
                            ->readOnly(),
                        TextInput::make('ip_address')
                            ->label(__('admin.resources.not_found_log.fields.ip_address'))
                            ->readOnly(),
                        Textarea::make('user_agent')
                            ->label(__('admin.resources.not_found_log.fields.user_agent'))
                            ->readOnly()
                            ->rows(3),
                    ]),
            ]);
    }
}

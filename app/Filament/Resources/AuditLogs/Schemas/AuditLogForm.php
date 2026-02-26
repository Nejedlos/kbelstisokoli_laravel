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
                Section::make('Základní informace')
                    ->columns(3)
                    ->schema([
                        TextInput::make('occurred_at')
                            ->label('Čas události')
                            ->disabled(),
                        TextInput::make('category')
                            ->label('Kategorie')
                            ->disabled(),
                        TextInput::make('event_key')
                            ->label('Klíč události')
                            ->disabled(),
                        TextInput::make('action')
                            ->label('Akce')
                            ->disabled(),
                        TextInput::make('severity')
                            ->label('Závažnost')
                            ->disabled(),
                        TextInput::make('source')
                            ->label('Zdroj')
                            ->disabled(),
                    ]),

                Section::make('Aktér a Předmět')
                    ->columns(2)
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('actor.name')
                                    ->label('Jméno aktéra')
                                    ->disabled(),
                                TextInput::make('actor_type')
                                    ->label('Typ Aktéra')
                                    ->disabled(),
                            ]),
                        Grid::make()
                            ->schema([
                                TextInput::make('subject_label')
                                    ->label('Název předmětu')
                                    ->disabled(),
                                TextInput::make('subject_type')
                                    ->label('Typ předmětu')
                                    ->disabled(),
                                TextInput::make('subject_id')
                                    ->label('ID předmětu')
                                    ->disabled(),
                            ]),
                    ]),

                Section::make('Kontext požadavku')
                    ->columns(2)
                    ->schema([
                        TextInput::make('url')
                            ->label('URL')
                            ->disabled(),
                        TextInput::make('route_name')
                            ->label('Název cesty')
                            ->disabled(),
                        TextInput::make('ip_address')
                            ->label('Anonymizovaná IP')
                            ->disabled(),
                        TextInput::make('request_id')
                            ->label('Request ID')
                            ->disabled(),
                        Textarea::make('user_agent_summary')
                            ->label('User Agent')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Section::make('Změny a Metadata')
                    ->schema([
                        Textarea::make('changes')
                            ->label('Změny')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                            ->rows(10),
                        Textarea::make('metadata')
                            ->label('Metadata')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                            ->rows(5),
                    ]),
            ]);
    }
}

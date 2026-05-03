<?php

namespace App\Filament\Resources\Dmarc\DmarcIncidentResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detaily incidentu')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Název')
                            ->disabled()
                            ->columnSpanFull(),
                        TextInput::make('domain')
                            ->label('Doména')
                            ->disabled(),
                        TextInput::make('source_ip')
                            ->label('Zdrojová IP')
                            ->disabled(),
                        TextInput::make('severity')
                            ->label('Závažnost')
                            ->disabled(),
                        Select::make('state')
                            ->label('Stav')
                            ->options([
                                'open' => 'Otevřeno',
                                'ack' => 'V řešení',
                                'resolved' => 'Vyřešeno',
                            ])
                            ->required(),
                    ]),

                Section::make('Analýza a akce')
                    ->schema([
                        Textarea::make('description')
                            ->label('Popis problému')
                            ->disabled()
                            ->rows(3),
                        Textarea::make('recommended_action')
                            ->label('Doporučená akce')
                            ->disabled()
                            ->rows(3),
                    ]),

                Section::make('Statistiky')
                    ->columns(3)
                    ->schema([
                        TextInput::make('occurrences_count')
                            ->label('Počet výskytů')
                            ->disabled(),
                        TextInput::make('first_seen_at')
                            ->label('Prvně spatřeno')
                            ->dateTime()
                            ->disabled(),
                        TextInput::make('last_seen_at')
                            ->label('Naposledy spatřeno')
                            ->dateTime()
                            ->disabled(),
                    ]),
            ]);
    }
}

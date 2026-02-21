<?php

namespace App\Filament\Resources\BasketballMatches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BasketballMatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('team_id')
                                    ->label('Náš tým')
                                    ->relationship('team', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('season_id')
                                    ->label('Sezóna')
                                    ->relationship('season', 'name')
                                    ->required(),
                                Select::make('opponent_id')
                                    ->label('Soupeř')
                                    ->relationship('opponent', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                DateTimePicker::make('scheduled_at')
                                    ->label('Datum a čas zápasu')
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Detaily zápasu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location')
                                    ->label('Místo konání')
                                    ->placeholder('např. Hala Kbely')
                                    ->default(null),
                                Toggle::make('is_home')
                                    ->label('Domácí zápas?')
                                    ->default(true)
                                    ->required(),
                                Select::make('status')
                                    ->label('Stav zápasu')
                                    ->options([
                                        'planned' => 'Plánováno',
                                        'completed' => 'Odehráno',
                                        'cancelled' => 'Zrušeno',
                                        'postponed' => 'Odloženo',
                                    ])
                                    ->required()
                                    ->default('planned'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('score_home')
                                    ->label('Skóre (Domácí)')
                                    ->numeric()
                                    ->default(null),
                                TextInput::make('score_away')
                                    ->label('Skóre (Hosté)')
                                    ->numeric()
                                    ->default(null),
                            ])
                            ->visible(fn ($get) => $get('status') === 'completed'),
                    ]),

                Section::make('Poznámky')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes_public')
                            ->label('Veřejná poznámka')
                            ->helperText('Zobrazí se fanouškům na webu.')
                            ->default(null)
                            ->columnSpanFull(),
                        Textarea::make('notes_internal')
                            ->label('Interní poznámka')
                            ->helperText('Pouze pro trenéry a adminy.')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

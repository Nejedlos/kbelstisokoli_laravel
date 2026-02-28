<?php

namespace App\Filament\Resources\BasketballMatches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                                Select::make('match_type')
                                    ->label('Typ zápasu')
                                    ->options([
                                        'mistrovske' => 'Mistrovské utkání',
                                        'poharove' => 'Pohárové utkání',
                                        'turnaj' => 'Turnaj',
                                        'pratelske' => 'Přátelské utkání',
                                    ])
                                    ->required()
                                    ->default('mistrovske')
                                    ->live(),
                                Select::make('teams')
                                    ->label('Týmy')
                                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->maxItems(fn ($get) => in_array($get('match_type'), ['mistrovske', 'poharove']) ? 1 : null)
                                    ->validationMessages([
                                        'max' => 'Pro mistrovské a pohárové zápasy lze vybrat pouze jeden tým.',
                                    ]),
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
                                        'scheduled' => 'Naplánováno (ze svazu)',
                                        'played' => 'Odehráno (ze svazu)',
                                        'completed' => 'Odehráno (ručně)',
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
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'played'])),
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

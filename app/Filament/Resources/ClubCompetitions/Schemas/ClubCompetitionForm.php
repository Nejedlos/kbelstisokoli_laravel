<?php

namespace App\Filament\Resources\ClubCompetitions\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClubCompetitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Klubová soutěž / Výzva')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Název soutěže')
                                    ->helperText('Např. Lumír Trophy, Střelec měsíce')
                                    ->required(),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique('club_competitions', 'slug', ignoreRecord: true),
                            ]),
                        TextInput::make('metric_description')
                            ->label('Metrika / Cíl')
                            ->helperText('Např. Nejvíc proměněných šestek za sezónu.')
                            ->placeholder('Popište, co se měří'),
                        Textarea::make('description')
                            ->label('Popis soutěže')
                            ->rows(3),
                    ]),

                Section::make('Pravidla a sezóna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('season_id')
                                    ->label('Sezóna')
                                    ->relationship('season', 'name')
                                    ->required(),
                                Select::make('status')
                                    ->label('Stav')
                                    ->options([
                                        'active' => 'Probíhá',
                                        'completed' => 'Ukončeno',
                                        'archived' => 'Archivováno',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ]),
                        Textarea::make('rules')
                            ->label('Detailní pravidla')
                            ->rows(4),
                        Toggle::make('is_public')
                            ->label('Zobrazit veřejně?')
                            ->default(true),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ClubEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClubEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Název akce')
                                    ->required()
                                    ->placeholder('např. Valná hromada, Brigáda, Soustředění'),
                                Select::make('event_type')
                                    ->label('Typ akce')
                                    ->options([
                                        'social' => 'Společenská akce',
                                        'meeting' => 'Schůzka / Porada',
                                        'camp' => 'Soustředění / Kemp',
                                        'volunteer' => 'Dobrovolnická akce / Brigáda',
                                        'other' => 'Ostatní',
                                    ])
                                    ->default('other')
                                    ->required(),
                                Select::make('teams')
                                    ->label('Určeno pro týmy')
                                    ->helperText('Ponechte prázdné, pokud je akce pro celý klub.')
                                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('location')
                                    ->label('Místo konání')
                                    ->placeholder('např. Klubovna, Hala Kbely')
                                    ->default(null),
                            ]),
                    ]),

                Section::make('Čas a dostupnost')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Začátek akce')
                                    ->native(false)
                                    ->required(),
                                DateTimePicker::make('ends_at')
                                    ->label('Konec akce')
                                    ->native(false)
                                    ->required(),
                                Toggle::make('is_public')
                                    ->label('Veřejná akce?')
                                    ->helperText('Pokud je vypnuto, uvidí ji pouze přihlášení členové.')
                                    ->default(true)
                                    ->required(),
                                Toggle::make('rsvp_enabled')
                                    ->label('Povolit RSVP (Docházku)?')
                                    ->helperText('Umožní členům potvrdit svou účast.')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Popis a poznámky')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('description')
                            ->label('Detailní popis akce')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

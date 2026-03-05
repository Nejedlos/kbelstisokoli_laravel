<?php

namespace App\Filament\Resources\ExternalTeamMappings\Schemas;

use App\Models\ExternalStatSource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalTeamMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->columns(2)
                    ->schema([
                        Select::make('source_key')
                            ->label('Zdroj')
                            ->options(ExternalStatSource::all()->pluck('name', 'slug'))
                            ->default('czbasketball')
                            ->searchable()
                            ->required(),
                        Select::make('team_id')
                            ->label('Interní tým')
                            ->relationship('team', 'name')
                            ->required(),
                        TextInput::make('external_team_id')
                            ->label('Externí ID týmu')
                            ->required()
                            ->helperText('Např. 7738 pro Muži E'),
                        TextInput::make('base_team_url')
                            ->label('URL týmu')
                            ->url()
                            ->required()
                            ->helperText('https://cz.basketball/tym/7738'),
                    ]),
            ]);
    }
}

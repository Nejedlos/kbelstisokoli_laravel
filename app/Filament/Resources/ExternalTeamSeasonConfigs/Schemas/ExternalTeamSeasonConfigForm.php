<?php

namespace App\Filament\Resources\ExternalTeamSeasonConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalTeamSeasonConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní identifikace')
                    ->columns(2)
                    ->schema([
                        Select::make('team_id')
                            ->label('Tým')
                            ->relationship('team', 'name')
                            ->required()
                            ->searchable(),
                        Select::make('season_id')
                            ->label('Sezóna')
                            ->relationship('season', 'name')
                            ->required()
                            ->searchable()
                            ->default(\App\Models\Season::where('is_active', true)->first()?->id),
                        TextInput::make('source_key')
                            ->label('Zdroj')
                            ->default('czbasketball')
                            ->required()
                            ->readOnly(),
                        TextInput::make('external_season_year')
                            ->label('Externí rok sezóny (y=)')
                            ->numeric()
                            ->required()
                            ->helperText('Např. 2025 pro sezónu 2025/2026'),
                    ]),
                Section::make('URL adresy')
                    ->columns(2)
                    ->schema([
                        TextInput::make('team_season_url')
                            ->label('URL soupisky')
                            ->url()
                            ->required()
                            ->helperText('https://cz.basketball/tym/7738?y=2025'),
                        TextInput::make('matches_list_url')
                            ->label('URL seznamu zápasů')
                            ->url()
                            ->required()
                            ->helperText('https://smo.cz.basketball/zapasy?c=7738&y=2025'),
                    ]),
                Section::make('Nastavení a stav')
                    ->columns(2)
                    ->schema([
                        TextInput::make('competition_label')
                            ->label('Název soutěže (nepovinné)'),
                        Toggle::make('is_enabled')
                            ->label('Aktivní pro synchronizaci')
                            ->default(true),
                        TextInput::make('last_synced_at')
                            ->label('Naposledy synchronizováno')
                            ->readOnly()
                            ->helperText('Nastavuje se automaticky po úspěšném běhu'),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\ExternalTeamSeasonConfigs\Schemas;

use App\Models\Season;
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
                            ->default(Season::where('is_active', true)->first()?->id),
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
                        TextInput::make('competition_url')
                            ->label(__('admin.resources.external_team_season_config.fields.competition_url'))
                            ->url()
                            ->helperText(__('admin.resources.external_team_season_config.helpers.competition_url')),
                    ]),
                Section::make('Nastavení a stav')
                    ->columns(2)
                    ->schema([
                        TextInput::make('competition_label')
                            ->label('Název soutěže (nepovinné)'),
                        TextInput::make('team_name_in_source')
                            ->label('Název týmu ve zdroji (nepovinné)')
                            ->helperText('Např. "Sokol Kbely E". Pomáhá s párováním v boxscoru.'),
                        Toggle::make('is_enabled')
                            ->label('Aktivní pro synchronizaci')
                            ->default(true),
                        Toggle::make('metadata.standings_enabled')
                            ->label(__('admin.resources.external_team_season_config.fields.standings_enabled'))
                            ->helperText(__('admin.resources.external_team_season_config.helpers.standings_enabled'))
                            ->default(false),
                        TextInput::make('last_synced_at')
                            ->label('Naposledy synchronizováno')
                            ->readOnly()
                            ->helperText('Nastavuje se automaticky po úspěšném běhu'),
                    ]),
            ]);
    }
}

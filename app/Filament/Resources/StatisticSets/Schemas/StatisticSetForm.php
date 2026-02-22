<?php

namespace App\Filament\Resources\StatisticSets\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StatisticSetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('StatisticSet Tabs')
                    ->tabs([
                        Tabs\Tab::make('Základní informace')
                            ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::INFO))
                            ->schema([
                                Section::make('Metadata')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Název sady')
                                            ->helperText('Např. Tabulka 1. ligy, Hráčské statistiky 2024')
                                            ->required(),
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique('statistic_sets', 'slug', ignoreRecord: true),
                                        Select::make('type')
                                            ->label('Typ statistik')
                                            ->options([
                                                'league_table' => 'Ligová tabulka',
                                                'player_stats' => 'Hráčské statistiky',
                                                'team_summary' => 'Týmový souhrn',
                                                'custom_competition' => 'Vlastní soutěž/tabulka',
                                            ])
                                            ->required(),
                                        Textarea::make('description')
                                            ->label('Popis')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Section::make('Nastavení a viditelnost')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('status')
                                                    ->label('Stav')
                                                    ->options([
                                                        'draft' => 'Koncept',
                                                        'published' => 'Publikováno',
                                                        'archived' => 'Archivováno',
                                                    ])
                                                    ->default('draft')
                                                    ->required(),
                                                Select::make('source_type')
                                                    ->label('Zdroj dat')
                                                    ->options([
                                                        'manual' => 'Ruční zadávání',
                                                        'external_import' => 'Externí import',
                                                        'hybrid' => 'Hybridní (import + úpravy)',
                                                    ])
                                                    ->default('manual')
                                                    ->required(),
                                                TextInput::make('sort_order')
                                                    ->label('Pořadí')
                                                    ->numeric()
                                                    ->default(0),
                                            ]),
                                        Toggle::make('is_visible')
                                            ->label('Zobrazit veřejně?')
                                            ->default(true),
                                    ]),
                            ]),

                        Tabs\Tab::make('Konfigurace sloupců')
                            ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::TABLE))
                            ->schema([
                                Section::make('Definice tabulky')
                                    ->description('Definujte sloupce, které se budou v této tabulce zobrazovat.')
                                    ->schema([
                                        Repeater::make('column_config')
                                            ->label('Sloupce')
                                            ->schema([
                                                TextInput::make('key')
                                                    ->label('Klíč (např. pts, avg)')
                                                    ->required(),
                                                TextInput::make('label')
                                                    ->label('Název (v záhlaví)')
                                                    ->required(),
                                                Select::make('type')
                                                    ->label('Typ dat')
                                                    ->options([
                                                        'number' => 'Číslo',
                                                        'text' => 'Text',
                                                        'percentage' => 'Procento',
                                                        'time' => 'Čas (minuty)',
                                                    ])
                                                    ->default('number'),
                                                Toggle::make('sortable')
                                                    ->label('Řaditelné?')
                                                    ->default(true),
                                            ])
                                            ->columns(4)
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                                    ]),
                            ]),

                        Tabs\Tab::make('Rozsah (Scope)')
                            ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::FILTER))
                            ->schema([
                                Section::make('Filtry a vazby')
                                    ->description('K čemu se tato sada statistik vztahuje (vazby jsou informativní pro renderování).')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('scope.season_id')
                                                    ->label('Sezóna')
                                                    ->options(\App\Models\Season::pluck('name', 'id'))
                                                    ->searchable(),
                                                Select::make('scope.team_id')
                                                    ->label('Tým')
                                                    ->options(\App\Models\Team::pluck('name', 'id'))
                                                    ->searchable(),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

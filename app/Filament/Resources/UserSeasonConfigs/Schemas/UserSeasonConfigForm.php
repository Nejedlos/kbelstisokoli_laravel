<?php

namespace App\Filament\Resources\UserSeasonConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserSeasonConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní nastavení')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Uživatel')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->hidden(fn ($livewire) => $livewire instanceof RelationManager),
                                Select::make('season_id')
                                    ->label('Sezóna')
                                    ->relationship('season', 'name')
                                    ->required(),
                                Select::make('financial_tariff_id')
                                    ->label('Finanční tarif')
                                    ->relationship('tariff', 'name')
                                    ->required(),
                                TextInput::make('opening_balance')
                                    ->label('Počáteční zůstatek')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Kč')
                                    ->helperText('Převod z minulé sezóny.'),
                            ]),
                    ]),

                Section::make('Období a osvobození')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('billing_start_month')
                                    ->label('Účtovat od (měsíc)')
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                                Select::make('billing_end_month')
                                    ->label('Účtovat do (měsíc)')
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                                Select::make('exemption_start_month')
                                    ->label('Osvobozen od (měsíc)')
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                                Select::make('exemption_end_month')
                                    ->label('Osvobozen do (měsíc)')
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Ostatní')
                    ->schema([
                        Toggle::make('track_attendance')
                            ->label('Hlídat docházku')
                            ->helperText('Pokud je vypnuto, nebudou se u tohoto uživatele generovat rozpory v docházce.')
                            ->default(true),
                    ]),
            ]);
    }

    protected static function getMonthsOptions(): array
    {
        return [
            1 => 'Leden', 2 => 'Únor', 3 => 'Březen', 4 => 'Duben',
            5 => 'Květen', 6 => 'Červen', 7 => 'Červenec', 8 => 'Srpen',
            9 => 'Září', 10 => 'Říjen', 11 => 'Listopad', 12 => 'Prosinec',
        ];
    }
}

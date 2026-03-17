<?php

namespace App\Filament\Resources\Seasons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->components([
                        TextInput::make('name')
                            ->label('Název sezóny')
                            ->placeholder('např. 2025/2026')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktivní sezóna')
                            ->required(),
                    ]),

                Section::make('Automatické pokuty')
                    ->description('Definujte výši pokut pro tuto sezónu. Pokuty se generují automaticky při uzavření docházky nebo importu statistik.')
                    ->icon(new HtmlString('<i class="fa-light fa-sack-dollar text-primary-500"></i>'))
                    ->components([
                        TextInput::make('fine_no_response')
                            ->label('Nezadání docházky')
                            ->helperText('Hráč neklikl na "přijdu" ani "nepřijdu" a nebyl na akci.')
                            ->numeric()
                            ->prefix('CZK')
                            ->default(0),
                        TextInput::make('fine_no_show')
                            ->label('Neomluvená absence')
                            ->helperText('Hráč potvrdil účast, ale na akci nedorazil.')
                            ->numeric()
                            ->prefix('CZK')
                            ->default(0),
                        TextInput::make('fine_unannounced_show')
                            ->label('Nenahlášená účast')
                            ->helperText('Hráč se nevyjádřil, ale na akci dorazil.')
                            ->numeric()
                            ->prefix('CZK')
                            ->default(0),
                        TextInput::make('fine_excused_show')
                            ->label('Účast i přes omluvu')
                            ->helperText('Hráč se omluvil, ale na akci přesto dorazil.')
                            ->numeric()
                            ->prefix('CZK')
                            ->default(0),
                        TextInput::make('fine_missed_free_throw')
                            ->label('Neproměněný trestný hod')
                            ->helperText('Pokuta za každý jeden neproměněný TH ze statistik.')
                            ->numeric()
                            ->prefix('CZK')
                            ->default(0),
                    ])->columns(2),
            ]);
    }
}

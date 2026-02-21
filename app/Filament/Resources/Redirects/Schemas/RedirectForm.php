<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní nastavení')
                    ->description('Definujte původní cestu a kam má být uživatel přesměrován.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('source_path')
                                    ->label('Původní cesta (Source)')
                                    ->helperText('Zadejte cestu začínající lomítkem (např. /stary-web/clanek).')
                                    ->placeholder('/stara-url')
                                    ->required()
                                    ->unique('redirects', 'source_path', ignoreRecord: true),

                                Select::make('match_type')
                                    ->label('Typ shody')
                                    ->options([
                                        'exact' => 'Přesná shoda (Exact)',
                                        'prefix' => 'Začíná na (Prefix)',
                                    ])
                                    ->default('exact')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('target_type')
                                    ->label('Typ cíle')
                                    ->options([
                                        'internal' => 'Interní cesta (v rámci webu)',
                                        'external' => 'Externí URL (jiný web)',
                                    ])
                                    ->default('internal')
                                    ->live()
                                    ->required(),

                                Select::make('status_code')
                                    ->label('Kód přesměrování')
                                    ->options([
                                        301 => '301 - Trvalé (Permanent)',
                                        302 => '302 - Dočasné (Found)',
                                    ])
                                    ->default(301)
                                    ->required(),
                            ]),

                        TextInput::make('target_path')
                            ->label('Cílová cesta')
                            ->helperText('Zadejte cestu v rámci tohoto webu (např. /novinky/novy-clanek).')
                            ->placeholder('/nova-url')
                            ->required(fn ($get) => $get('target_type') === 'internal')
                            ->visible(fn ($get) => $get('target_type') === 'internal'),

                        TextInput::make('target_url')
                            ->label('Externí cílová URL')
                            ->helperText('Zadejte kompletní URL včetně https:// (např. https://facebook.com/kbelstisokoli).')
                            ->placeholder('https://...')
                            ->url()
                            ->required(fn ($get) => $get('target_type') === 'external')
                            ->visible(fn ($get) => $get('target_type') === 'external'),
                    ]),

                Section::make('Doplňující informace')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktivní přesměrování')
                                    ->default(true),

                                TextInput::make('priority')
                                    ->label('Priorita')
                                    ->helperText('Vyšší číslo znamená dřívější vyhodnocení.')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Textarea::make('notes')
                            ->label('Interní poznámka')
                            ->placeholder('Proč bylo přesměrování vytvořeno?')
                            ->rows(3),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\FinanceCharges\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FinanceChargeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní údaje předpisu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Člen (Uživatel)')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('charge_type')
                                    ->label('Typ platby')
                                    ->options([
                                        'membership_fee' => 'Členský příspěvek',
                                        'camp_fee' => 'Soustředění / Kemp',
                                        'tournament_fee' => 'Turnaj',
                                        'other' => 'Ostatní',
                                    ])
                                    ->default('membership_fee')
                                    ->required(),
                                TextInput::make('title')
                                    ->label('Název / Účel')
                                    ->placeholder('např. Příspěvky Jaro 2024')
                                    ->required()
                                    ->columnSpanFull(),
                                Textarea::make('description')
                                    ->label('Podrobný popis')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Finanční detaily')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('amount_total')
                                    ->label('Celková částka')
                                    ->numeric()
                                    ->prefix('CZK')
                                    ->required(),
                                DatePicker::make('due_date')
                                    ->label('Datum splatnosti')
                                    ->native(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('period_from')
                                    ->label('Období od')
                                    ->native(false),
                                DatePicker::make('period_to')
                                    ->label('Období do')
                                    ->native(false),
                            ]),
                    ]),

                Section::make('Nastavení a stav')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Stav')
                                    ->options([
                                        'draft' => 'Koncept',
                                        'open' => 'Otevřeno (K úhradě)',
                                        'partially_paid' => 'Částečně zaplaceno',
                                        'paid' => 'Zaplaceno',
                                        'cancelled' => 'Zrušeno',
                                        'overdue' => 'Po splatnosti',
                                    ])
                                    ->default('open')
                                    ->required(),
                                Toggle::make('is_visible_to_member')
                                    ->label('Zobrazit členovi?')
                                    ->helperText('Pokud je vypnuto, člen položku neuvidí ve svém portálu.')
                                    ->default(true),
                            ]),
                        Textarea::make('notes_internal')
                            ->label('Interní poznámka (admin)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

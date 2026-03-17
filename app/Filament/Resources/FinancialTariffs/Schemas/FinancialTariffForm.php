<?php

namespace App\Filament\Resources\FinancialTariffs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class FinancialTariffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní nastavení')
                    ->components([
                        TextInput::make('name')
                            ->label('Název tarifu')
                            ->placeholder('např. Členský příspěvek - Elite')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Typ platby')
                            ->options([
                                'flat' => 'Paušál / Splátky',
                                'per_event' => 'Platba za akci (Pay-per-event)',
                                'prepaid' => 'Předplacený balíček akcí (Prepaid)',
                            ])
                            ->default('flat')
                            ->required()
                            ->live(),
                        TextInput::make('base_amount')
                            ->label(fn ($get) => match($get('type')) {
                                'per_event' => 'Částka za jednu akci',
                                'prepaid' => 'Cena za celý balíček',
                                default => 'Celková částka / Základ'
                            })
                            ->numeric()
                            ->required()
                            ->prefix('CZK'),
                        TextInput::make('prepaid_events_count')
                            ->label('Počet akcí v balíčku')
                            ->numeric()
                            ->required(fn ($get) => $get('type') === 'prepaid')
                            ->visible(fn ($get) => $get('type') === 'prepaid'),
                        TextInput::make('extra_event_amount')
                            ->label('Cena za akci po vyčerpání balíčku')
                            ->numeric()
                            ->required(fn ($get) => $get('type') === 'prepaid')
                            ->visible(fn ($get) => $get('type') === 'prepaid')
                            ->prefix('CZK'),
                        Select::make('unit')
                            ->label('Časová jednotka')
                            ->options([
                                'month' => 'Měsíc',
                                'season' => 'Sezóna',
                                'event' => 'Akce',
                            ])
                            ->default('month')
                            ->required(),
                    ])->columns(2),

                Section::make('Splátkový kalendář')
                    ->description('Definujte jednotlivé splátky pro paušální tarify. Systém je vygeneruje automaticky při přiřazení tarifu.')
                    ->visible(fn ($get) => $get('type') === 'flat')
                    ->components([
                        Repeater::make('installment_plan')
                            ->label('Plán splátek')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Popis splátky')
                                    ->placeholder('např. 1. splátka')
                                    ->required(),
                                TextInput::make('amount')
                                    ->label('Částka')
                                    ->numeric()
                                    ->required()
                                    ->prefix('CZK'),
                                DatePicker::make('due_date')
                                    ->label('Datum splatnosti')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Přidat splátku'),
                    ]),

                Section::make('Automatizace a pokuty')
                    ->description('Nastavte, zda se pro tento tarif mají automaticky počítat pokuty.')
                    ->icon(new HtmlString('<i class="fa-light fa-robot text-primary-500"></i>'))
                    ->components([
                        Toggle::make('calculate_attendance_fines')
                            ->label('Hlídat a pokutovat docházku')
                            ->helperText('Pokud je zapnuto, systém bude generovat pokuty za nevyjádření, pozdní omluvy atd.')
                            ->default(false),
                        Toggle::make('calculate_th_fines')
                            ->label('Pokutovat neproměněné trestné hody')
                            ->helperText('Pokud je zapnuto, systém bude generovat pokuty za neproměněné TH ze statistik.')
                            ->default(false),
                    ])->columns(2),

                Section::make('Doplňující informace')
                    ->components([
                        Textarea::make('description')
                            ->label('Interní popis')
                            ->rows(3),
                    ]),
            ]);
    }
}

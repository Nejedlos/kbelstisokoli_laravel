<?php

namespace App\Filament\Resources\FinancePayments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FinancePaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní údaje platby')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Plátce (Člen)')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Přiřaďte plátce, pokud je znám.'),
                                TextInput::make('amount')
                                    ->label('Částka')
                                    ->numeric()
                                    ->prefix('CZK')
                                    ->required(),
                                DateTimePicker::make('paid_at')
                                    ->label('Datum a čas přijetí')
                                    ->native(false)
                                    ->default(now())
                                    ->required(),
                                Select::make('payment_method')
                                    ->label('Způsob úhrady')
                                    ->options([
                                        'bank_transfer' => 'Bankovní převod',
                                        'cash' => 'Hotovost',
                                        'other' => 'Jiné',
                                    ])
                                    ->default('bank_transfer')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Identifikace platby')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('variable_symbol')
                                    ->label('Variabilní symbol')
                                    ->placeholder('např. 2024001'),
                                TextInput::make('transaction_reference')
                                    ->label('ID transakce / Reference')
                                    ->placeholder('např. bankovní referenční číslo'),
                            ]),
                        Textarea::make('source_note')
                            ->label('Poznámka z výpisu / Původní text')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Stav a audit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Stav záznamu')
                                    ->options([
                                        'recorded' => 'Zapsáno',
                                        'confirmed' => 'Potvrzeno',
                                        'reversed' => 'Stornováno / Vráceno',
                                    ])
                                    ->default('recorded')
                                    ->required(),
                                Select::make('recorded_by_id')
                                    ->label('Zapsal')
                                    ->relationship('recordedBy', 'name')
                                    ->disabled()
                                    ->default(auth()->id()),
                            ]),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\FinancialTariffs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FinancialTariffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Název tarifu')
                    ->required()
                    ->maxLength(255),
                TextInput::make('base_amount')
                    ->label('Základní částka')
                    ->numeric()
                    ->required()
                    ->prefix('Kč'),
                Select::make('unit')
                    ->label('Jednotka')
                    ->options([
                        'month' => 'Měsíc',
                        'season' => 'Sezóna',
                    ])
                    ->default('month')
                    ->required(),
                Textarea::make('description')
                    ->label('Popis')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Opponents\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OpponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('city')
                    ->default(null),
                TextInput::make('logo')
                    ->default(null),
            ]);
    }
}

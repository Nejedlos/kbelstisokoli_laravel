<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Název akce')
                            ->placeholder('např. Soustředění, Valná hromada')
                            ->required(),
                        TextInput::make('location')
                            ->label('Místo konání')
                            ->default(null),
                        DateTimePicker::make('starts_at')
                            ->label('Začátek akce')
                            ->native(false)
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label('Konec akce')
                            ->native(false)
                            ->required(),
                        Toggle::make('is_public')
                            ->label('Veřejná akce?')
                            ->helperText('Pokud je vypnuto, uvidí ji pouze přihlášení členové.')
                            ->default(true)
                            ->required(),
                    ]),
                Textarea::make('description')
                    ->label('Popis akce')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Trainings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('teams')
                            ->label('Týmy')
                            ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('location')
                            ->label('Místo (Hala/Tělocvična)')
                            ->placeholder('např. Hala Kbely')
                            ->default(null),
                        DateTimePicker::make('starts_at')
                            ->label('Začátek tréninku')
                            ->native(false)
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label('Konec tréninku')
                            ->native(false)
                            ->required(),
                    ]),
                Textarea::make('notes')
                    ->label('Poznámka / Program')
                    ->helperText('Např. zaměření tréninku nebo speciální instrukce.')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}

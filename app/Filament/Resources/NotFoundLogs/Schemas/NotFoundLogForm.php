<?php

namespace App\Filament\Resources\NotFoundLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotFoundLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informace o chybě')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('url')
                                    ->label('URL')
                                    ->readOnly(),
                                TextInput::make('hits_count')
                                    ->label('Počet výskytů')
                                    ->numeric()
                                    ->readOnly(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('last_seen_at')
                                    ->label('Naposledy viděno')
                                    ->readOnly(),
                                TextInput::make('status')
                                    ->label('Stav')
                                    ->readOnly(),
                            ]),
                    ]),

                Section::make('Detaily o požadavku')
                    ->schema([
                        TextInput::make('referer')
                            ->label('Referer')
                            ->readOnly(),
                        TextInput::make('ip_address')
                            ->label('IP adresa')
                            ->readOnly(),
                        Textarea::make('user_agent')
                            ->label('User Agent')
                            ->readOnly()
                            ->rows(3),
                    ]),
            ]);
    }
}

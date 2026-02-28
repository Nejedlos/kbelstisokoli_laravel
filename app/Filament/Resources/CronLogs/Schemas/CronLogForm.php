<?php

namespace App\Filament\Resources\CronLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CronLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('task.name')
                    ->label('Úloha')
                    ->disabled(),
                DateTimePicker::make('started_at')
                    ->label('Začátek')
                    ->disabled(),
                DateTimePicker::make('finished_at')
                    ->label('Konec')
                    ->disabled(),
                TextInput::make('status')
                    ->label('Stav')
                    ->disabled(),
                TextInput::make('duration_ms')
                    ->label('Trvání (ms)')
                    ->disabled(),
                Textarea::make('output')
                    ->label('Výstup')
                    ->rows(10)
                    ->disabled()
                    ->columnSpanFull(),
                Textarea::make('error_message')
                    ->label('Chybová zpráva')
                    ->rows(5)
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\PerformanceTestResults\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PerformanceTestResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Stránka')
                                    ->required(),
                                TextInput::make('url')
                                    ->label('URL')
                                    ->required(),
                                Select::make('section')
                                    ->label('Sekce')
                                    ->options([
                                        'public' => 'Veřejná',
                                        'member' => 'Členská',
                                        'admin' => 'Admin',
                                    ])
                                    ->required(),
                                Select::make('scenario')
                                    ->label('Scénář')
                                    ->options([
                                        'standard' => 'Standard',
                                        'aggressive' => 'Aggressive',
                                        'ultra' => 'Ultra',
                                    ])
                                    ->required(),
                            ]),
                    ]),
                Section::make('Metriky')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('duration_ms')
                                    ->label('Doba (ms)')
                                    ->numeric(),
                                TextInput::make('query_count')
                                    ->label('Počet dotazů')
                                    ->numeric(),
                                TextInput::make('query_time_ms')
                                    ->label('Čas DB (ms)')
                                    ->numeric(),
                                TextInput::make('memory_mb')
                                    ->label('Paměť (MB)')
                                    ->numeric(),
                                Toggle::make('opcache_enabled')
                                    ->label('Opcache aktivní'),
                            ]),
                    ]),
                DateTimePicker::make('created_at')
                    ->label('Vytvořeno')
                    ->disabled(),
            ]);
    }
}

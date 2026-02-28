<?php

namespace App\Filament\Resources\CronTasks\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CronTaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní nastavení úlohy')
                    ->description('Definujte příkaz a časový rozvrh spouštění.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Název úlohy')
                                    ->required()
                                    ->placeholder('např. Upomínky RSVP'),
                                TextInput::make('command')
                                    ->label('Artisan příkaz')
                                    ->required()
                                    ->placeholder('rsvp:reminders')
                                    ->helperText('Zadejte název Artisan příkazu.'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('expression')
                                    ->label('Cron výraz (Schedule)')
                                    ->required()
                                    ->default('* * * * *')
                                    ->placeholder('* * * * *')
                                    ->helperText('Formát: minuta hodina den měsíc den_v_týdnu.'),
                                TextInput::make('priority')
                                    ->label('Priorita')
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Toggle::make('is_active')
                            ->label('Aktivní')
                            ->default(true),
                    ]),

                Section::make('Stav posledního běhu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('last_run_at')
                                    ->label('Poslední spuštění')
                                    ->content(fn ($record) => $record?->last_run_at?->format('d.m.Y H:i:s') ?? 'Nikdy'),
                                Placeholder::make('last_status')
                                    ->label('Poslední stav')
                                    ->content(fn ($record) => $record?->last_status ?? '-'),
                            ]),
                        Placeholder::make('last_error_message')
                            ->label('Poslední chyba')
                            ->content(fn ($record) => $record?->last_error_message ?? 'Bez chyb')
                            ->visible(fn ($record) => ! empty($record?->last_error_message)),
                    ])
                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),

                Section::make('Popis a detaily')
                    ->collapsed()
                    ->schema([
                        Textarea::make('description')
                            ->label('Popis účelu úlohy')
                            ->rows(3),
                    ]),
            ]);
    }
}

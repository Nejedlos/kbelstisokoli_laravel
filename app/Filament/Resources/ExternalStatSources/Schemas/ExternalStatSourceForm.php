<?php

namespace App\Filament\Resources\ExternalStatSources\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class ExternalStatSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Zdroj statistik')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Název zdroje')
                                    ->required(),
                                Select::make('source_type')
                                    ->label('Typ zdroje')
                                    ->options([
                                        'html_table' => 'HTML tabulka',
                                        'page_extract' => 'Extrakce ze stránky',
                                        'api' => 'API rozhraní',
                                    ])
                                    ->default('html_table')
                                    ->required(),
                            ]),
                        TextInput::make('source_url')
                            ->label('URL zdroje')
                            ->url()
                            ->required(),
                    ]),

                Section::make('Konfigurace Ingestu')
                    ->description('Nastavení pro automatickou extrakci a mapování dat.')
                    ->schema([
                        Textarea::make('extractor_config')
                            ->label('Extractor Config (JSON)')
                            ->helperText('Definujte CSS selektory nebo indexy tabulek.')
                            ->rows(5)
                            ->fontFamily('monospace'),
                        Textarea::make('mapping_config')
                            ->label('Mapping Config (JSON)')
                            ->helperText('Definujte mapování polí z externího zdroje na naše klíče.')
                            ->rows(5)
                            ->fontFamily('monospace'),
                    ]),

                Section::make('Stav a logy')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktivní zdroj')
                                    ->default(true),
                                Placeholder::make('last_run_at')
                                    ->label('Poslední spuštění')
                                    ->content(fn ($record) => $record?->last_run_at?->format('d.m.Y H:i') ?? 'Nikdy'),
                            ]),
                        TextInput::make('last_status')
                            ->label('Poslední stav')
                            ->disabled(),
                        Textarea::make('notes')
                            ->label('Poznámky')
                            ->rows(2),
                    ]),
            ]);
    }
}

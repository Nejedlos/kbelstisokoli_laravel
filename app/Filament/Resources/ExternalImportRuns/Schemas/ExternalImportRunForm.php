<?php

namespace App\Filament\Resources\ExternalImportRuns\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalImportRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->columns(3)
                    ->schema([
                        TextInput::make('source_key')
                            ->label('Zdroj')
                            ->readOnly(),
                        TextInput::make('run_type')
                            ->label('Typ běhu')
                            ->readOnly(),
                        TextInput::make('status')
                            ->label('Stav')
                            ->readOnly(),
                        TextInput::make('season.name')
                            ->label('Sezóna')
                            ->readOnly(),
                        TextInput::make('team.name')
                            ->label('Tým')
                            ->readOnly(),
                        TextInput::make('target_external_id')
                            ->label('Cílové externí ID')
                            ->readOnly(),
                    ]),
                Section::make('Časové údaje')
                    ->columns(3)
                    ->schema([
                        TextInput::make('started_at')
                            ->label('Zahájeno')
                            ->readOnly(),
                        TextInput::make('finished_at')
                            ->label('Dokončeno')
                            ->readOnly(),
                        TextInput::make('created_at')
                            ->label('Vytvořeno')
                            ->readOnly(),
                    ]),
                Section::make('Výsledky')
                    ->columns(3)
                    ->schema([
                        TextInput::make('extracted_count')
                            ->label('Extrahováno')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('imported_count')
                            ->label('Importováno')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('skipped_count')
                            ->label('Přeskočeno')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('content_hash')
                            ->label('Hash obsahu')
                            ->columnSpanFull()
                            ->readOnly(),
                        TextInput::make('error_summary')
                            ->label('Chyba')
                            ->columnSpanFull()
                            ->readOnly()
                            ->hidden(fn ($get) => ! $get('error_summary')),
                    ]),
                Section::make('Metadata a doplňky')
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->disableAddingRows()
                            ->disableDeletingRows()
                            ->disableEditingKeys()
                            ->disableEditingValues(),
                    ]),
            ]);
    }
}

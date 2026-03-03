<?php

namespace App\Filament\Resources\LegacyImportBatches\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class LegacyImportBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Název dávky')
                            ->placeholder('např. Statistiky 2015-2020')
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('status')
                            ->label('Stav')
                            ->readOnly()
                            ->hidden(fn (string $operation): bool => $operation === 'create'),
                    ]),

                Section::make('Nahrání souborů')
                    ->description('Vyberte HTML soubory pro import. Každý soubor bude zpracován samostatně ve frontě.')
                    ->schema([
                        FileUpload::make('uploaded_files')
                            ->label('HTML soubory')
                            ->multiple()
                            ->directory('legacy_import/temp')
                            ->acceptedFileTypes(['text/html', 'application/xhtml+xml'])
                            ->storeFiles(true)
                            ->reorderable()
                            ->appendFiles()
                            ->dehydrated(false) // Nezpracovávat automaticky do modelu Batch
                            ->helperText('Povoleny jsou pouze HTML soubory.')
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        Placeholder::make('files_info')
                            ->label('Soubory v této dávce')
                            ->content(fn ($record) => $record ? $record->files()->count() . ' souborů' : '-')
                            ->visible(fn (string $operation): bool => $operation !== 'create'),
                    ]),
            ]);
    }
}

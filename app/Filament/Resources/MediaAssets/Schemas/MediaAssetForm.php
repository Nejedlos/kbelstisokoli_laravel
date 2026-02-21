<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Grid;
use Filament\Schemas\Schema;

class MediaAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Soubor')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('file')
                            ->label('Nahrát soubor')
                            ->collection('default')
                            ->required()
                            ->columnSpanFull()
                            ->hint('Podporovány jsou obrázky a dokumenty (PDF).'),
                    ]),

                Section::make('Metadata')
                    ->description('Popisné informace o médiu pro SEO a přístupnost.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Název (Title)')
                                    ->helperText('Interní název nebo titulek média.')
                                    ->maxLength(255),

                                TextInput::make('alt_text')
                                    ->label('Alternativní text (Alt)')
                                    ->helperText('Popis pro čtečky obrazovky a vyhledávače.')
                                    ->maxLength(255),
                            ]),

                        Textarea::make('caption')
                            ->label('Popisek (Caption)')
                            ->helperText('Zobrazuje se pod obrázkem na webu.')
                            ->rows(2),

                        Textarea::make('description')
                            ->label('Dlouhý popis')
                            ->rows(3),
                    ]),

                Section::make('Nastavení')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('type')
                                    ->label('Typ média')
                                    ->options([
                                        'image' => 'Obrázek',
                                        'document' => 'Dokument (PDF, DOC...)',
                                        'video' => 'Video (odkaz)',
                                        'other' => 'Ostatní',
                                    ])
                                    ->default('image')
                                    ->required(),

                                Select::make('access_level')
                                    ->label('Úroveň přístupu')
                                    ->options([
                                        'public' => 'Veřejné',
                                        'member' => 'Pouze pro členy',
                                        'private' => 'Soukromé / Interní',
                                    ])
                                    ->default('public')
                                    ->required(),

                                Toggle::make('is_public')
                                    ->label('Zobrazit v knihovně?')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}

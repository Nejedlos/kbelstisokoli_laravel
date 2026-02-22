<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                            // Dynamický disk podle úrovně přístupu
                            ->disk(fn ($get) => $get('access_level') === 'public' ? 'media_public' : 'media_private')
                            // Čištění názvu souboru při nahrávání
                            ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                $title = $get('title');
                                $ext = $file->getClientOriginalExtension();
                                if ($title) {
                                    return Str::slug($title) . '.' . $ext;
                                }
                                return Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                            })
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

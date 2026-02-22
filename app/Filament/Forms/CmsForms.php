<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class CmsForms
{
    public static function getSeoSection(): Section
    {
        return Section::make('SEO Metadata')
            ->description('Nastavení pro vyhledávače a sociální sítě. Pomáhá lepšímu zobrazení na Google a Facebooku.')
            ->relationship('seo')
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('SEO Titulek (Browser Title)')
                            ->helperText('Doporučená délka do 60 znaků. Pokud zůstane prázdné, použije se název stránky + globální přípona.')
                            ->maxLength(100),

                        TextInput::make('canonical_url')
                            ->label('Kanonická URL')
                            ->helperText('Pokud má stránka duplicitu jinde, uveďte originální URL. Standardně se použije aktuální URL.')
                            ->url()
                            ->maxLength(255),
                    ]),

                Textarea::make('description')
                    ->label('SEO Popis (Meta Description)')
                    ->helperText('Stručný popis obsahu pro vyhledávače (cca 150-160 znaků). Pokud zůstane prázdné, zkusíme vygenerovat z obsahu.')
                    ->rows(3)
                    ->maxLength(255),

                Grid::make(2)
                    ->schema([
                        Toggle::make('robots_index')
                            ->label('Indexovat ve vyhledávačích (Index)')
                            ->default(true),
                        Toggle::make('robots_follow')
                            ->label('Sledovat odkazy (Follow)')
                            ->default(true),
                    ]),

                Section::make('Sociální sítě (Open Graph & Twitter)')
                    ->description('Jak se bude stránka zobrazovat při sdílení na Facebooku, LinkedIn nebo X (Twitter).')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make([
                                    TextInput::make('og_title')
                                        ->label('OG Titulek')
                                        ->placeholder('Ponechte prázdné pro použití SEO titulku'),

                                    Textarea::make('og_description')
                                        ->label('OG Popis')
                                        ->rows(3)
                                        ->placeholder('Ponechte prázdné pro použití SEO popisu'),

                                    Select::make('twitter_card')
                                        ->label('Twitter Card')
                                        ->options([
                                            'summary' => 'Malý náhled',
                                            'summary_large_image' => 'Velký náhled (doporučeno)',
                                        ])
                                        ->default('summary_large_image'),
                                ]),

                                FileUpload::make('og_image')
                                    ->label('Sdílený obrázek (OG Image)')
                                    ->helperText('Doporučený rozměr 1200x630px. Pokud nevyberete, použije se náhledový obrázek stránky nebo globální logo.')
                                    ->image()
                                    ->directory('seo/og-images'),
                            ]),
                    ]),

                Section::make('Pokročilé / Vývojář')
                    ->description('Nastavení pro experty.')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('structured_data_override')
                            ->label('Vlastní strukturovaná data (JSON-LD)')
                            ->helperText('Vložte validní JSON pole. Bude přidáno k automaticky generovaným datům.')
                            ->rows(5)
                            ->fontFamily('monospace'),
                    ]),
            ])
            ->collapsible()
            ->collapsed();
    }
}

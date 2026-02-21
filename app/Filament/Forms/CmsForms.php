<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

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
                            ->label('SEO Titulek')
                            ->helperText('Doporučená délka do 60 znaků. Pokud zůstane prázdné, použije se název stránky.')
                            ->maxLength(100),

                        TextInput::make('keywords')
                            ->label('Klíčová slova')
                            ->helperText('Slova oddělená čárkou (např. basketbal, Kbely, sokoli).'),
                    ]),

                Textarea::make('description')
                    ->label('SEO Popis (Meta Description)')
                    ->helperText('Stručný popis obsahu pro vyhledávače (cca 150-160 znaků).')
                    ->rows(3)
                    ->maxLength(255),

                Section::make('Sociální sítě (Open Graph)')
                    ->description('Jak se bude stránka zobrazovat při sdílení na Facebooku nebo X (Twitter).')
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
                                ]),

                                FileUpload::make('og_image')
                                    ->label('Sdílený obrázek (OG Image)')
                                    ->helperText('Doporučený rozměr 1200x630px.')
                                    ->image()
                                    ->directory('seo/og-images'),
                            ]),
                    ]),
            ])
            ->collapsible()
            ->collapsed();
    }
}

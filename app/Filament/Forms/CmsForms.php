<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class CmsForms
{
    public static function getSeoSection(): Section
    {
        return Section::make('SEO Metadata')
            ->description('Nastavení pro vyhledávače a sociální sítě.')
            ->relationship('seo')
            ->schema([
                TextInput::make('title')
                    ->label('SEO Titulek')
                    ->helperText('Pokud zůstane prázdné, použije se název stránky/novinky.')
                    ->maxLength(60),

                Textarea::make('description')
                    ->label('SEO Popis')
                    ->helperText('Stručný popis obsahu pro vyhledávače (cca 150-160 znaků).')
                    ->maxLength(160),

                TextInput::make('keywords')
                    ->label('Klíčová slova')
                    ->helperText('Oddělená čárkou.'),

                Group::make([
                    TextInput::make('og_title')
                        ->label('OG Titulek (Facebook/X)')
                        ->maxLength(60),

                    Textarea::make('og_description')
                        ->label('OG Popis (Facebook/X)')
                        ->maxLength(160),

                    FileUpload::make('og_image')
                        ->label('OG Obrázek')
                        ->image()
                        ->directory('seo-images'),
                ])->columns(1),
            ])
            ->collapsed();
    }
}

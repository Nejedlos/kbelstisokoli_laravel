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
    public static function getSeoSection(bool $withRelationship = true): Section
    {
        $section = Section::make('SEO Metadata')
            ->description('Nastavení pro vyhledávače a sociální sítě. Pomáhá lepšímu zobrazení na Google a Facebooku.');

        if ($withRelationship) {
            $section->relationship('seo');
        }

        return $section->schema([
                \Filament\Schemas\Components\Tabs::make('SeoLanguageVersions')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Čeština')
                            ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-language mr-1"></i>'))
                            ->schema([
                                TextInput::make('title.cs')
                                    ->label('SEO Titulek (CZ)')
                                    ->helperText('Doporučená délka do 60 znaků. Pokud zůstane prázdné, použije se název stránky + globální přípona.')
                                    ->maxLength(100),

                                Textarea::make('description.cs')
                                    ->label('SEO Popis (CZ)')
                                    ->helperText('Stručný popis obsahu pro vyhledávače (cca 150-160 znaků). Pokud zůstane prázdné, zkusíme vygenerovat z obsahu.')
                                    ->rows(3)
                                    ->maxLength(255),

                                TextInput::make('keywords.cs')
                                    ->label('Klíčová slova (CZ)')
                                    ->helperText('Oddělujte čárkou.'),

                                TextInput::make('og_title.cs')
                                    ->label('OG Titulek (CZ)')
                                    ->placeholder('Ponechte prázdné pro použití SEO titulku'),

                                Textarea::make('og_description.cs')
                                    ->label('OG Popis (CZ)')
                                    ->rows(3)
                                    ->placeholder('Ponechte prázdné pro použití SEO popisu'),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('English')
                            ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-language mr-1"></i>'))
                            ->schema([
                                TextInput::make('title.en')
                                    ->label('SEO Title (EN)')
                                    ->helperText('Recommended length up to 60 characters.')
                                    ->maxLength(100),

                                Textarea::make('description.en')
                                    ->label('SEO Description (EN)')
                                    ->helperText('Brief description for search engines.')
                                    ->rows(3)
                                    ->maxLength(255),

                                TextInput::make('keywords.en')
                                    ->label('Keywords (EN)')
                                    ->helperText('Separate with commas.'),

                                TextInput::make('og_title.en')
                                    ->label('OG Title (EN)')
                                    ->placeholder('Leave empty to use SEO Title'),

                                Textarea::make('og_description.en')
                                    ->label('OG Description (EN)')
                                    ->rows(3)
                                    ->placeholder('Leave empty to use SEO Description'),
                            ]),
                    ]),

                Section::make('Technické a globální nastavení')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('canonical_url')
                                    ->label('Kanonická URL')
                                    ->helperText('Pokud má stránka duplicitu jinde, uveďte originální URL. Standardně se použije aktuální URL.')
                                    ->url()
                                    ->maxLength(255),

                                Select::make('twitter_card')
                                    ->label('Twitter Card')
                                    ->options([
                                        'summary' => 'Malý náhled',
                                        'summary_large_image' => 'Velký náhled (doporučeno)',
                                    ])
                                    ->default('summary_large_image'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('robots_index')
                                    ->label('Indexovat ve vyhledávačích (Index)')
                                    ->default(true),
                                Toggle::make('robots_follow')
                                    ->label('Sledovat odkazy (Follow)')
                                    ->default(true),
                            ]),

                        FileUpload::make('og_image')
                            ->label('Sdílený obrázek (OG Image)')
                            ->helperText('Doporučený rozměr 1200x630px. Pokud nevyberete, použije se náhledový obrázek stránky nebo globální logo.')
                            ->image()
                            ->directory('seo/og-images'),

                        Textarea::make('structured_data_override')
                            ->label('Vlastní strukturovaná data (JSON-LD)')
                            ->helperText('Vložte validní JSON pole. Bude přidáno k automaticky generovaným datům.')
                            ->rows(5),
                    ]),
            ])
            ->collapsible()
            ->collapsed();
    }
}

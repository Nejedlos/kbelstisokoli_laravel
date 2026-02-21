<?php

namespace App\Services\Cms;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;

class BlockRegistry
{
    /**
     * Získá všechny dostupné bloky pro Page Builder.
     */
    public static function getBlocks(): array
    {
        return [
            self::getHeroBlock(),
            self::getRichTextBlock(),
            self::getImageBlock(),
            self::getCallToActionBlock(),
            self::getCardsGridBlock(),
            self::getStatsCardsBlock(),
            self::getNewsListingBlock(),
            self::getMatchesListingBlock(),
            self::getGalleryBlock(),
            self::getContactInfoBlock(),
            self::getCustomHtmlBlock(),
        ];
    }

    protected static function getHeroBlock(): Block
    {
        return Block::make('hero')
            ->label('Hero sekce')
            ->icon('heroicon-o-sparkles')
            ->schema([
                TextInput::make('headline')->label('Hlavní nadpis')->required(),
                TextInput::make('subheadline')->label('Podnadpis'),
                TextInput::make('cta_label')->label('Text tlačítka'),
                TextInput::make('cta_url')->label('URL tlačítka'),
                FileUpload::make('image')->label('Obrázek na pozadí')->image()->directory('blocks/hero'),
                Select::make('alignment')
                    ->label('Zarovnání')
                    ->options([
                        'left' => 'Vlevo',
                        'center' => 'Na střed',
                        'right' => 'Vpravo',
                    ])
                    ->default('center'),
                Toggle::make('overlay')->label('Tmavý překryv')->default(true),
            ]);
    }

    protected static function getRichTextBlock(): Block
    {
        return Block::make('rich_text')
            ->label('Textový blok')
            ->icon('heroicon-o-document-text')
            ->schema([
                RichEditor::make('content')
                    ->label('Obsah')
                    ->required(),
            ]);
    }

    protected static function getImageBlock(): Block
    {
        return Block::make('image')
            ->label('Obrázek')
            ->icon('heroicon-o-photo')
            ->schema([
                FileUpload::make('image')->label('Obrázek')->image()->required()->directory('blocks/images'),
                TextInput::make('caption')->label('Popisek'),
                TextInput::make('alt')->label('Alt text (pro SEO/čtečky)'),
            ]);
    }

    protected static function getCallToActionBlock(): Block
    {
        return Block::make('cta')
            ->label('Výzva k akci (CTA)')
            ->icon('heroicon-o-megaphone')
            ->schema([
                TextInput::make('title')->label('Titulek')->required(),
                TextInput::make('button_text')->label('Text tlačítka')->required(),
                TextInput::make('button_url')->label('URL tlačítka')->required(),
                Select::make('style')
                    ->label('Styl')
                    ->options([
                        'primary' => 'Primární',
                        'secondary' => 'Sekundární',
                        'outline' => 'Obrysové',
                    ])
                    ->default('primary'),
            ]);
    }

    protected static function getCardsGridBlock(): Block
    {
        return Block::make('cards_grid')
            ->label('Mřížka karet')
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                TextInput::make('title')->label('Nadpis mřížky'),
                Repeater::make('cards')
                    ->label('Karty')
                    ->schema([
                        TextInput::make('title')->label('Titulek karty')->required(),
                        Textarea::make('description')->label('Popis'),
                        TextInput::make('link')->label('Odkaz'),
                        FileUpload::make('icon')->label('Ikona/Obrázek')->image(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->collapsible()
                    ->grid(2),
            ]);
    }

    protected static function getStatsCardsBlock(): Block
    {
        return Block::make('stats_cards')
            ->label('Statistické karty')
            ->icon('heroicon-o-chart-bar')
            ->schema([
                Repeater::make('stats')
                    ->label('Statistiky')
                    ->schema([
                        TextInput::make('value')->label('Hodnota (např. 150)')->required(),
                        TextInput::make('label')->label('Popisek (např. Členů)')->required(),
                        TextInput::make('icon')->label('Heroicon name (volitelně)'),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->grid(3),
            ]);
    }

    protected static function getNewsListingBlock(): Block
    {
        return Block::make('news_listing')
            ->label('Výpis novinek')
            ->icon('heroicon-o-newspaper')
            ->schema([
                TextInput::make('title')->label('Nadpis sekce')->default('Aktuality'),
                TextInput::make('limit')->label('Počet novinek')->numeric()->default(3),
                // Použijeme model pro získání kategorií, protože Select::relationship nemusí v Builderu fungovat správně při smazání
                Select::make('category_id')
                    ->label('Filtrovat podle kategorie')
                    ->options(\App\Models\PostCategory::pluck('name', 'id'))
                    ->searchable(),
            ]);
    }

    protected static function getMatchesListingBlock(): Block
    {
        return Block::make('matches_listing')
            ->label('Výpis zápasů')
            ->icon('heroicon-o-trophy')
            ->schema([
                TextInput::make('title')->label('Nadpis sekce')->default('Následující zápasy'),
                Select::make('type')
                    ->label('Typ zápasů')
                    ->options([
                        'upcoming' => 'Nadcházející',
                        'latest' => 'Poslední výsledky',
                    ])
                    ->default('upcoming'),
                TextInput::make('limit')->label('Počet zápasů')->numeric()->default(5),
            ]);
    }

    protected static function getGalleryBlock(): Block
    {
        return Block::make('gallery')
            ->label('Galerie')
            ->icon('heroicon-o-rectangle-group')
            ->schema([
                TextInput::make('title')->label('Nadpis galerie'),
                FileUpload::make('images')
                    ->label('Obrázky')
                    ->multiple()
                    ->image()
                    ->reorderable()
                    ->directory('blocks/gallery'),
            ]);
    }

    protected static function getContactInfoBlock(): Block
    {
        return Block::make('contact_info')
            ->label('Kontakt / Info')
            ->icon('heroicon-o-information-circle')
            ->schema([
                TextInput::make('title')->label('Nadpis')->default('Kontaktujte nás'),
                Textarea::make('address')->label('Adresa'),
                TextInput::make('email')->label('Email')->email(),
                TextInput::make('phone')->label('Telefon'),
                Toggle::make('show_map')->label('Zobrazit mapu')->default(false),
            ]);
    }

    protected static function getCustomHtmlBlock(): Block
    {
        return Block::make('custom_html')
            ->label('Vlastní HTML / Embed')
            ->icon('heroicon-o-code-bracket')
            ->schema([
                Textarea::make('html')
                    ->label('HTML kód')
                    ->helperText('Zadejte surový HTML kód, skripty nebo embed kódy.')
                    ->rows(10)
                    ->required(),
            ]);
    }
}

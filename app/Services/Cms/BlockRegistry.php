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
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;

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
            self::getStatsTableBlock(),
        ];
    }

    protected static function withAdvancedSettings(array $schema): array
    {
        return array_merge($schema, [self::getAdvancedSettingsSection()]);
    }

    protected static function getAdvancedSettingsSection(): Section
    {
        return Section::make('Pokročilé nastavení (Expert)')
            ->description('Nastavení pro vývojáře a superadminy. Umožňuje přímé stylování bloku.')
            ->icon('heroicon-o-code-bracket')
            ->collapsible()
            ->collapsed()
            ->visible(fn () => auth()->user()?->can('manage_advanced_settings'))
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('custom_id')
                            ->label('Vlastní ID (Anchor)')
                            ->helperText('ID elementu pro přímé odkazu nebo JS (např. #kontakt).')
                            ->placeholder('kontakt-sekce'),
                        TextInput::make('custom_class')
                            ->label('Vlastní CSS třídy')
                            ->helperText('Přidejte libovolné Tailwind nebo vlastní třídy oddělené mezerou.')
                            ->placeholder('bg-gray-100 py-20'),
                    ]),
                Repeater::make('custom_attributes')
                    ->label('Vlastní HTML atributy')
                    ->helperText('Přidejte data atributy nebo jiné vlastnosti (např. data-aos="fade-up").')
                    ->schema([
                        TextInput::make('name')
                            ->label('Název atributu')
                            ->required(),
                        TextInput::make('value')
                            ->label('Hodnota')
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
            ]);
    }

    protected static function getHeroBlock(): Block
    {
        return Block::make('hero')
            ->label('Hero sekce')
            ->icon('heroicon-o-sparkles')
            ->label(fn (array $state): ?string => $state['headline'] ?? 'Hero sekce')
            ->schema(self::withAdvancedSettings([
                Section::make('Hlavní obsah')
                    ->schema([
                        TextInput::make('headline')
                            ->label('Hlavní nadpis')
                            ->helperText('Hlavní poutavý nadpis sekce.')
                            ->required(),
                        TextInput::make('subheadline')
                            ->label('Podnadpis')
                            ->helperText('Doplňující text pod nadpisem.'),
                    ])->columns(2),

                Section::make('Tlačítko (CTA)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cta_label')
                                    ->label('Text tlačítka')
                                    ->placeholder('Zjistit více'),
                                TextInput::make('cta_url')
                                    ->label('URL tlačítka')
                                    ->placeholder('/kontakt nebo https://...'),
                            ]),
                    ]),

                Section::make('Vzhled a styl')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('variant')
                                    ->label('Varianta rozvržení')
                                    ->options([
                                        'standard' => 'Standardní (obrazek vpravo)',
                                        'centered' => 'Vycentrované (pozadí)',
                                        'minimal' => 'Minimální (bez obrázku)',
                                    ])
                                    ->default('standard')
                                    ->required(),
                                Select::make('alignment')
                                    ->label('Zarovnání textu')
                                    ->options([
                                        'left' => 'Vlevo',
                                        'center' => 'Na střed',
                                        'right' => 'Vpravo',
                                    ])
                                    ->default('left'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('media_asset_id')
                                    ->label('Obrázek / Fallback')
                                    ->relationship('mediaAsset', 'title', fn ($query) => $query->where('type', 'image'))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Obrázek z knihovny (nebo fallback pro video).'),
                                TextInput::make('video_url')
                                    ->label('Video pozadí (URL)')
                                    ->placeholder('assets/video/hero.mp4')
                                    ->helperText('Cesta k MP4 souboru (relativně k public/).'),
                            ]),
                        Toggle::make('overlay')
                            ->label('Tmavý překryv obrázku')
                            ->helperText('Zlepší čitelnost textu na světlém obrázku.')
                            ->default(true),
                    ]),
            ]));
    }

    protected static function getRichTextBlock(): Block
    {
        return Block::make('rich_text')
            ->label('Textový blok')
            ->icon('heroicon-o-document-text')
            ->label(fn (array $state): ?string => strip_tags($state['content'] ?? 'Textový blok'))
            ->schema(self::withAdvancedSettings([
                RichEditor::make('content')
                    ->label('Formátovaný text')
                    ->helperText('Používejte nadpisy H2 nebo H3 pro strukturu obsahu.')
                    ->required(),
            ]));
    }

    protected static function getImageBlock(): Block
    {
        return Block::make('image')
            ->label('Obrázek')
            ->icon('heroicon-o-photo')
            ->label(fn (array $state): ?string => $state['caption'] ?? 'Obrázek')
            ->schema(self::withAdvancedSettings([
                Grid::make(2)
                    ->schema([
                        Select::make('media_asset_id')
                            ->label('Obrázek z knihovny')
                            ->relationship('mediaAsset', 'title', fn ($query) => $query->where('type', 'image'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Section::make('Nastavení obrázku')
                            ->schema([
                                TextInput::make('caption')
                                    ->label('Viditelný popisek')
                                    ->helperText('Zobrazí se pod obrázkem. Ponechte prázdné pro použití popisku z knihovny.'),
                                Select::make('width_class')
                                    ->label('Šířka obrázku')
                                    ->options([
                                        'max-w-full' => 'Plná šířka obsahu',
                                        'max-w-4xl' => 'Široký',
                                        'max-w-2xl' => 'Střední',
                                        'max-w-md' => 'Malý',
                                    ])
                                    ->default('max-w-full'),
                            ]),
                    ]),
            ]));
    }

    protected static function getCallToActionBlock(): Block
    {
        return Block::make('cta')
            ->label('Výzva k akci (CTA)')
            ->icon('heroicon-o-megaphone')
            ->label(fn (array $state): ?string => $state['title'] ?? 'CTA blok')
            ->schema(self::withAdvancedSettings([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Titulek')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('button_text')
                            ->label('Text tlačítka')
                            ->default('Klikněte zde')
                            ->required(),
                        TextInput::make('button_url')
                            ->label('Odkaz (URL)')
                            ->placeholder('/kontakt')
                            ->required(),
                        Select::make('style')
                            ->label('Vizuální styl')
                            ->options([
                                'primary' => 'Klubová červená (Primární)',
                                'secondary' => 'Tmavá (Sekundární)',
                                'outline' => 'Obrysové tlačítko',
                            ])
                            ->default('primary'),
                    ]),
            ]));
    }

    protected static function getCardsGridBlock(): Block
    {
        return Block::make('cards_grid')
            ->label('Mřížka karet')
            ->icon('heroicon-o-squares-2x2')
            ->label(fn (array $state): ?string => $state['title'] ?? 'Mřížka karet')
            ->schema(self::withAdvancedSettings([
                TextInput::make('title')
                    ->label('Nadpis celé sekce')
                    ->placeholder('Naše přednosti'),

                Select::make('columns')
                    ->label('Počet sloupců')
                    ->options([
                        2 => '2 sloupce',
                        3 => '3 sloupce',
                        4 => '4 sloupce',
                    ])
                    ->default(3),

                Repeater::make('cards')
                    ->label('Jednotlivé karty')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titulek karty')
                            ->required(),
                        Textarea::make('description')
                            ->label('Stručný popis')
                            ->rows(2),
                        TextInput::make('link')
                            ->label('Odkaz (volitelně)')
                            ->placeholder('https://...'),
                        FileUpload::make('icon')
                            ->label('Ikona nebo obrázek')
                            ->image()
                            ->disk(config('filesystems.uploads.disk'))
                            ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/') . '/blocks/cards'),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->collapsible()
                    ->collapsed()
                    ->grid(2),
            ]));
    }

    protected static function getStatsCardsBlock(): Block
    {
        return Block::make('stats_cards')
            ->label('Statistické údaje')
            ->icon('heroicon-o-chart-bar')
            ->label(fn (array $state): ?string => 'Statistické údaje (' . count($state['stats'] ?? []) . ')')
            ->schema(self::withAdvancedSettings([
                Repeater::make('stats')
                    ->label('Statistiky')
                    ->schema([
                        TextInput::make('value')
                            ->label('Číselná hodnota')
                            ->placeholder('např. 250')
                            ->required(),
                        TextInput::make('label')
                            ->label('Popisek')
                            ->placeholder('např. Aktivních hráčů')
                            ->required(),
                        TextInput::make('icon')
                            ->label('Heroicon (název)')
                            ->placeholder('user-group'),
                    ])
                    ->itemLabel(fn (array $state): ?string => ($state['value'] ?? '') . ' ' . ($state['label'] ?? ''))
                    ->grid(3),
            ]));
    }

    protected static function getNewsListingBlock(): Block
    {
        return Block::make('news_listing')
            ->label('Výpis novinek')
            ->icon('heroicon-o-newspaper')
            ->label(fn (array $state): ?string => $state['title'] ?? 'Výpis novinek')
            ->schema(self::withAdvancedSettings([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Nadpis sekce')
                            ->default('Aktuality'),
                        Select::make('limit')
                            ->label('Počet zobrazených položek')
                            ->options([
                                3 => '3 novinky',
                                6 => '6 novinek',
                                9 => '9 novinek',
                            ])
                            ->default(3),
                        Select::make('category_id')
                            ->label('Filtrovat podle kategorie')
                            ->options(\App\Models\PostCategory::pluck('name', 'id'))
                            ->placeholder('Všechny kategorie')
                            ->searchable(),
                        Select::make('layout')
                            ->label('Rozvržení')
                            ->options([
                                'grid' => 'Mřížka',
                                'list' => 'Seznam pod sebou',
                            ])
                            ->default('grid'),
                    ]),
            ]));
    }

    protected static function getMatchesListingBlock(): Block
    {
        return Block::make('matches_listing')
            ->label('Výpis zápasů')
            ->icon('heroicon-o-trophy')
            ->label(fn (array $state): ?string => $state['title'] ?? 'Výpis zápasů')
            ->schema(self::withAdvancedSettings([
                Grid::make(3)
                    ->schema([
                        TextInput::make('title')
                            ->label('Nadpis sekce')
                            ->default('Následující zápasy'),
                        Select::make('type')
                            ->label('Typ zápasů')
                            ->options([
                                'upcoming' => 'Nadcházející',
                                'latest' => 'Poslední výsledky',
                            ])
                            ->default('upcoming'),
                        TextInput::make('limit')
                            ->label('Limit')
                            ->numeric()
                            ->default(5),
                    ]),
            ]));
    }

    protected static function getGalleryBlock(): Block
    {
        return Block::make('gallery')
            ->label('Galerie')
            ->icon('heroicon-o-rectangle-group')
            ->label(fn (array $state): ?string => $state['title'] ?? 'Galerie')
            ->schema(self::withAdvancedSettings([
                TextInput::make('title')
                    ->label('Nadpis sekce')
                    ->placeholder('Fotky z turnaje'),
                Select::make('gallery_id')
                    ->label('Vybrat galerii')
                    ->relationship('gallery', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Vyberte existující galerii ze systému.'),
                Select::make('layout')
                    ->label('Styl galerie')
                    ->options([
                        'grid' => 'Mřížka (stejné čtverce)',
                        'masonry' => 'Mozaika (původní poměry)',
                    ])
                    ->default('grid'),
            ]));
    }

    protected static function getContactInfoBlock(): Block
    {
        return Block::make('contact_info')
            ->label('Kontakt / Info')
            ->icon('heroicon-o-information-circle')
            ->label(fn (array $state): ?string => $state['title'] ?? 'Kontakt')
            ->schema(self::withAdvancedSettings([
                TextInput::make('title')
                    ->label('Nadpis')
                    ->default('Kontaktujte nás'),
                Grid::make(2)
                    ->schema([
                        Textarea::make('address')
                            ->label('Adresa / Sídlo')
                            ->rows(3),
                        Grid::make(1)
                            ->schema([
                                TextInput::make('email')
                                    ->label('E-mailová adresa')
                                    ->email(),
                                TextInput::make('phone')
                                    ->label('Telefonní číslo'),
                            ]),
                    ]),
                        Toggle::make('show_map')
                    ->label('Zobrazit interaktivní mapu')
                    ->default(false),
            ]));
    }

    protected static function getCustomHtmlBlock(): Block
    {
        $canUseRawHtml = auth()->user()?->can('use_raw_html') ?? false;

        return Block::make('custom_html')
            ->label('Vlastní HTML / Embed')
            ->icon('heroicon-o-code-bracket')
            ->label(fn (array $state): ?string => ($state['mode'] ?? '') === 'raw' ? 'Vlastní HTML (Raw)' : 'Bezpečný Embed')
            ->schema(self::withAdvancedSettings([
                Select::make('mode')
                    ->label('Režim vkládání')
                    ->options([
                        'safe' => 'Bezpečný Embed (Iframe, Youtube, Facebook)',
                        'raw' => 'Surový HTML kód (Pouze pro zkušené)',
                    ])
                    ->default('safe')
                    ->live()
                    ->required(),

                Placeholder::make('raw_warning')
                    ->label('Upozornění')
                    ->content(new HtmlString('<span class="text-danger-600 font-bold">Pozor: Surové HTML může rozbít vzhled webu nebo představovat bezpečnostní riziko.</span>'))
                    ->visible(fn ($get) => $get('mode') === 'raw'),

                Textarea::make('html')
                    ->label('Kód k vložení')
                    ->helperText(fn ($get) => $get('mode') === 'raw'
                        ? 'Zadejte čisté HTML, CSS nebo JS.'
                        : 'Vložte <iframe> kód nebo URL pro embed.')
                    ->rows(10)
                    ->required()
                    ->disabled(fn ($get) => $get('mode') === 'raw' && !$canUseRawHtml),

                Placeholder::make('permission_denied')
                    ->label('Přístup odepřen')
                    ->content(new HtmlString('<span class="text-danger-500">Nemáte oprávnění pro vkládání surového HTML kódu. Kontaktujte administrátora.</span>'))
                    ->visible(fn ($get) => $get('mode') === 'raw' && !$canUseRawHtml),
            ]));
    }

    protected static function getStatsTableBlock(): Block
    {
        return Block::make('stats_table')
            ->label('Statistická tabulka')
            ->icon('heroicon-o-table-cells')
            ->label(fn (array $state): ?string => $state['title'] ?? 'Statistická tabulka')
            ->schema(self::withAdvancedSettings([
                TextInput::make('title')
                    ->label('Nadpis tabulky')
                    ->default('Statistiky'),
                Select::make('statistic_set_id')
                    ->label('Vyberte sadu statistik')
                    ->options(\App\Models\StatisticSet::where('status', 'published')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Grid::make(2)
                    ->schema([
                        TextInput::make('limit')
                            ->label('Počet řádků (limit)')
                            ->numeric()
                            ->default(10),
                        Toggle::make('show_link')
                            ->label('Zobrazit odkaz na detail')
                            ->default(true),
                    ]),
            ]));
    }
}

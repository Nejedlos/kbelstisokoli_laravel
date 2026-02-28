<?php

namespace App\Filament\Forms;

use App\Support\IconHelper;
use App\Support\Icons\AppIcon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

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
                        ->placeholder(self::getUploadPlaceholder('Nahrajte obrázek pro sociální sítě', 'Doporučený rozměr 1200x630px'))
                        ->image()
                        ->disk(config('filesystems.uploads.disk'))
                        ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/').'/seo/og-images'),

                    Textarea::make('structured_data_override')
                        ->label('Vlastní strukturovaná data (JSON-LD)')
                        ->helperText('Vložte validní JSON pole. Bude přidáno k automaticky generovaným datům.')
                        ->rows(5),
                ]),
        ])
            ->collapsible()
            ->collapsed();
    }

    public static function getUploadPlaceholder(?string $title = null, ?string $description = null, string|AppIcon $iconKey = IconHelper::UPLOAD): HtmlString
    {
        $title ??= 'Klikněte nebo přetáhněte soubory sem';
        $icon = IconHelper::render($iconKey, 'fal')->toHtml();
        // Nahradíme fa-fw za fa-2xl pro větší ikonu
        $icon = str_replace('fa-fw', 'fa-2xl', $icon);

        $html = <<<HTML
            <div class="flex flex-col items-center justify-center py-12 px-6 group/dropzone transition-all duration-300">
                <div class="w-20 h-20 mb-6 rounded-3xl bg-white dark:bg-gray-800 flex items-center justify-center border border-gray-100 dark:border-gray-700 text-primary-500 transition-all duration-500 group-hover/dropzone:scale-110 group-hover/dropzone:rotate-3 group-hover/dropzone:bg-primary-500 group-hover/dropzone:text-white group-hover/dropzone:border-primary-400 shadow-xl shadow-gray-200/50 dark:shadow-none">
                    {$icon}
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2 transition-colors duration-300 group-hover/dropzone:text-primary-600 dark:group-hover/dropzone:text-primary-400">
                    {$title}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center max-w-[320px] leading-relaxed">
                    {$description}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3 opacity-40 transition-all duration-300 group-hover/dropzone:opacity-100 group-hover/dropzone:translate-y-1">
                    <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">JPG</span>
                    <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">PNG</span>
                    <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">WEBP</span>
                    <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.1em] rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">HEIC</span>
                </div>
            </div>
HTML;

        return new HtmlString($html);
    }
}

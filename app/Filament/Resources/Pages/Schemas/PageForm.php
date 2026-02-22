<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Forms\CmsForms;
use App\Services\Cms\BlockRegistry;
use Filament\Forms\Components\Builder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Page Tabs')
                    ->tabs([
                        Tabs\Tab::make('Obsah')
                            ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::COPY))
                            ->schema([
                                Section::make('Základní informace')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Titulek stránky')
                                            ->helperText('Hlavní název stránky, zobrazí se v menu a nadpisu okna.')
                                            ->required(),
                                        TextInput::make('slug')
                                            ->label('URL adresa (slug)')
                                            ->helperText('Část URL za lomítkem. Např. "o-nas". Musí být unikátní.')
                                            ->required()
                                            ->unique('pages', 'slug', ignoreRecord: true),
                                    ])->columns(2),

                                Section::make('Vizuální editor')
                                    ->description('Skládejte obsah stránky z připravených bloků.')
                                    ->schema([
                                        Builder::make('content')
                                            ->label('Obsahové bloky')
                                            ->blocks(BlockRegistry::getBlocks())
                                            ->columnSpanFull()
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->blockNumbers(false)
                                            ->addActionLabel('Přidat nový blok obsahu'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Nastavení')
                            ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::SETTINGS))
                            ->schema([
                                Section::make('Stav a viditelnost')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('status')
                                                    ->label('Stav stránky')
                                                    ->options([
                                                        'draft' => 'Koncept (neviditelný na webu)',
                                                        'published' => 'Publikováno (veřejné)',
                                                    ])
                                                    ->default('draft')
                                                    ->required(),

                                                Toggle::make('is_visible')
                                                    ->label('Zobrazit v navigaci / seznamech?')
                                                    ->helperText('Pokud je vypnuto, stránka je dostupná pouze přes přímý odkaz.')
                                                    ->default(true),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::GLOBE))
                            ->schema([
                                CmsForms::getSeoSection(),
                            ]),

                        Tabs\Tab::make('Vývojář')
                            ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::CODE))
                            ->visible(fn () => auth()->user()?->can('manage_advanced_settings'))
                            ->schema([
                                Section::make('Vlastní kódy a skripty')
                                    ->description('Vložte kód, který se provede pouze na této stránce.')
                                    ->schema([
                                        Textarea::make('head_code')
                                            ->label('Kód do <head>')
                                            ->helperText('Např. vlastní CSS <style> nebo meta tagy.')
                                            ->rows(10)
                                            ->fontFamily('monospace'),

                                        Textarea::make('footer_code')
                                            ->label('Kód před </body>')
                                            ->helperText('Např. měřící kódy nebo vlastní JavaScript <script>.')
                                            ->rows(10)
                                            ->fontFamily('monospace'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Forms\CmsForms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Post Tabs')
                    ->tabs([
                        Tabs\Tab::make('Obsah')
                            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PAGES))
                            ->schema([
                                Section::make('Základní informace')
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::INFO))
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Titulek novinky')
                                            ->helperText('Hlavní nadpis článku.')
                                            ->required(),

                                        TextInput::make('slug')
                                            ->label('URL adresa (slug)')
                                            ->helperText('Část URL za lomítkem. Musí být unikátní.')
                                            ->required()
                                            ->unique('posts', 'slug', ignoreRecord: true),

                                        Select::make('category_id')
                                            ->label('Kategorie')
                                            ->helperText('Vyberte téma novinky pro lepší přehlednost.')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ])->columns(2),

                                Section::make('Text článku')
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PEN_NIB))
                                    ->schema([
                                        Textarea::make('excerpt')
                                            ->label('Perex (stručný výtah)')
                                            ->helperText('Zobrazuje se v přehledu novinek. Krátký úvod do článku.')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        RichEditor::make('content')
                                            ->label('Hlavní obsah článku')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Publikace a média')
                            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PHOTO_FILM))
                            ->schema([
                                Section::make('Stav publikace')
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::ANNOUNCEMENTS))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('status')
                                                    ->label('Stav')
                                                    ->options([
                                                        'draft' => 'Koncept (skrytý)',
                                                        'published' => 'Publikováno (veřejné)',
                                                    ])
                                                    ->default('draft')
                                                    ->required(),

                                                DateTimePicker::make('publish_at')
                                                    ->label('Datum a čas publikace')
                                                    ->helperText('Pokud nastavíte budoucí datum, článek se zobrazí až v daný čas.')
                                                    ->native(false),
                                            ]),

                                        Toggle::make('is_visible')
                                            ->label('Zobrazit na webu?')
                                            ->helperText('Globální vypínač viditelnosti.')
                                            ->default(true),
                                    ]),

                                Section::make('Média')
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::IMAGE))
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('featured_image')
                                            ->label('Hlavní náhledový obrázek')
                                            ->helperText('Tento obrázek se zobrazí v seznamu novinek a v záhlaví článku.')
                                            ->collection('featured_image')
                                            ->disk('media_public') // Veřejný prostor pro články
                                            ->image()
                                            ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                                $title = $get('title');
                                                $ext = $file->getClientOriginalExtension();
                                                if ($title) {
                                                    return \Illuminate\Support\Str::slug($title) . '.' . $ext;
                                                }
                                                return \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                                            }),
                                    ]),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::SEO))
                            ->schema([
                                CmsForms::getSeoSection(),
                            ]),

                        Tabs\Tab::make('Vývojář')
                            ->visible(fn () => auth()->user()?->can('manage_advanced_settings'))
                            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::CODE))
                            ->schema([
                                Section::make('Vlastní kódy a skripty')
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::TERMINAL))
                                    ->description('Vložte kód, který se provede pouze pro tuto novinku.')
                                    ->schema([
                                        Textarea::make('head_code')
                                            ->label('Kód do <head>')
                                            ->rows(10),

                                        Textarea::make('footer_code')
                                            ->label('Kód před </body>')
                                            ->rows(10),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

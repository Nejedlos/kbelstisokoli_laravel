<?php

namespace App\Filament\Resources\Partners\Schemas;

use App\Support\IconHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make(new HtmlString(IconHelper::render(IconHelper::INFO) . ' Základní informace'))
                            ->schema([
                                TextInput::make('name')
                                    ->label('Název partnera')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set, $operation) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique('partners', 'slug', ignoreRecord: true),

                                Select::make('type')
                                    ->label('Typ partnera')
                                    ->options([
                                        'main_partner' => 'Hlavní partner',
                                        'general_partner' => 'Generální partner',
                                        'partner' => 'Partner',
                                        'media_partner' => 'Mediální partner',
                                    ])
                                    ->required()
                                    ->default('partner')
                                    ->native(false),

                                TextInput::make('website_url')
                                    ->label('Webová stránka (URL)')
                                    ->url()
                                    ->columnSpanFull(),

                                Toggle::make('opened_in_new_tab')
                                    ->label('Otevírat v novém okně')
                                    ->default(true),
                            ])
                            ->columnSpan(2),

                        Section::make(new HtmlString(IconHelper::render(IconHelper::SETTINGS) . ' Stav a řazení'))
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktivní')
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->label('Zvýrazněný')
                                    ->default(false),

                                TextInput::make('sort_order')
                                    ->label('Pořadí')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make(new HtmlString(IconHelper::render(IconHelper::IMAGE) . ' Loga (Cesty k assetům)'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('logo_path_png')
                                    ->label('Cesta k PNG logu')
                                    ->placeholder('assets/img/partners/logo.png')
                                    ->helperText('Relativní cesta od public/ složky'),

                                TextInput::make('logo_path_webp')
                                    ->label('Cesta k WebP logu')
                                    ->placeholder('assets/img/partners/logo.webp')
                                    ->helperText('Relativní cesta od public/ složky'),
                            ]),
                    ]),

                Section::make(new HtmlString(IconHelper::render(IconHelper::GLOBE) . ' Texty a překlady'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label.cs')
                                    ->label('Štítek / Label (CZ)')
                                    ->placeholder('Hlavní partner týmu'),

                                TextInput::make('label.en')
                                    ->label('Label (EN)')
                                    ->placeholder('Main team partner'),

                                Textarea::make('description.cs')
                                    ->label('Popis (CZ)')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Textarea::make('description.en')
                                    ->label('Description (EN)')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make(new HtmlString(IconHelper::render(IconHelper::BRANDING) . ' Umístění na webu'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('show_on_homepage')
                                    ->label('Homepage (celkově)')
                                    ->default(true),

                                Toggle::make('show_below_hero')
                                    ->label('Strip pod Hero sekcí')
                                    ->default(true),

                                Toggle::make('show_in_footer')
                                    ->label('Patička (Footer)')
                                    ->default(true),

                                Toggle::make('show_on_match_pages')
                                    ->label('Stránky zápasů')
                                    ->default(true),

                                Toggle::make('show_on_contact_page')
                                    ->label('Kontakt')
                                    ->default(true),

                                Toggle::make('show_on_recruitment_page')
                                    ->label('Nábor')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Galleries\Schemas;

use App\Filament\Forms\CmsForms;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Gallery Tabs')
                    ->tabs([
                        Tabs\Tab::make('Obsah')
                            ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-image fa-fw"></i>'))
                            ->schema([
                                Section::make('Základní informace')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label('Název galerie')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),

                                                TextInput::make('slug')
                                                    ->label('Slug (URL)')
                                                    ->required()
                                                    ->unique('galleries', 'slug', ignoreRecord: true),
                                            ]),

                                        Textarea::make('description')
                                            ->label('Popis galerie')
                                            ->rows(3),
                                    ]),

                                Section::make('Nastavení a cover')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('variant')
                                                    ->label('Styl zobrazení')
                                                    ->options([
                                                        'grid' => 'Mřížka (čtverce)',
                                                        'masonry' => 'Mozaika',
                                                    ])
                                                    ->default('grid')
                                                    ->required(),

                                                Select::make('cover_asset_id')
                                                    ->label('Úvodní obrázek (Cover)')
                                                    ->relationship('coverAsset', 'title')
                                                    ->searchable()
                                                    ->preload()
                                                    ->helperText('Vyberte obrázek z knihovny médií.'),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('is_public')
                                                    ->label('Veřejná galerie')
                                                    ->default(true),

                                                Toggle::make('is_visible')
                                                    ->label('Viditelná v seznamu')
                                                    ->default(true),

                                                DateTimePicker::make('published_at')
                                                    ->label('Datum publikace')
                                                    ->native(false)
                                                    ->default(now()),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->icon(new \Illuminate\Support\HtmlString('<i class="fa-light fa-globe fa-fw"></i>'))
                            ->schema([
                                CmsForms::getSeoSection(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Forms\CmsForms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Post Tabs')
                    ->tabs([
                        Tabs\Tab::make('Obsah')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titulek')
                                    ->required(),

                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->helperText('Vyplňte ručně, nebo použijte vlastní konvenci. Musí být jedinečné.')
                                    ->required()
                                    ->unique('posts', 'slug', ignoreRecord: true),

                                Select::make('category_id')
                                    ->label('Kategorie')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),

                                Textarea::make('excerpt')
                                    ->label('Perex (stručný výtah)')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                RichEditor::make('content')
                                    ->label('Obsah článku')
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Publikace')
                            ->icon('heroicon-o-paper-airplane')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('status')
                                            ->label('Stav')
                                            ->options([
                                                'draft' => 'Koncept',
                                                'published' => 'Publikováno',
                                            ])
                                            ->default('draft')
                                            ->required(),

                                        DateTimePicker::make('publish_at')
                                            ->label('Datum publikace')
                                            ->native(false),
                                    ]),

                                FileUpload::make('featured_image')
                                    ->label('Náhledový obrázek')
                                    ->image()
                                    ->directory('posts'),

                                \Filament\Forms\Components\Toggle::make('is_visible')
                                    ->label('Zobrazit na webu?')
                                    ->default(true),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                CmsForms::getSeoSection(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

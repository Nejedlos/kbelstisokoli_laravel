<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Forms\CmsForms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
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
                            ->icon('heroicon-o-document-duplicate')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titulek stránky')
                                    ->required(),

                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->helperText('Vyplňte ručně, nebo použijte vlastní konvenci. Musí být jedinečné.')
                                    ->required()
                                    ->unique('pages', 'slug', ignoreRecord: true),

                                Placeholder::make('blocks_notice')
                                    ->label('Obsahové bloky')
                                    ->content('Plnohodnotný blokový editor bude implementován v dalším kroku. Nyní můžete použít základní editor níže.')
                                    ->columnSpanFull(),

                                RichEditor::make('content')
                                    ->label('Obsah stránky (prozatímní)')
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Nastavení')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Select::make('status')
                                    ->label('Stav')
                                    ->options([
                                        'draft' => 'Koncept',
                                        'published' => 'Publikováno',
                                    ])
                                    ->default('draft')
                                    ->required(),

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

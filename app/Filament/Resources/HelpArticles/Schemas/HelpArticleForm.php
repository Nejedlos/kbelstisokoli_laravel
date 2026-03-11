<?php

namespace App\Filament\Resources\HelpArticles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class HelpArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('is_customized_notice')
                    ->label('')
                    ->content(new HtmlString('
                        <div class="flex items-center gap-3 p-4 bg-primary-50 border border-primary-100 rounded-xl text-primary-800 mb-6">
                            <i class="fa-light fa-circle-info text-xl text-primary-500"></i>
                            <div>
                                <p class="font-bold">Systémově spravovaný článek</p>
                                <p class="text-sm opacity-90">Tento článek je synchronizován ze zdrojových souborů. Pokud jej upravíte, bude označen jako "Uživatelsky upraveno" a přestane se automaticky aktualizovat.</p>
                            </div>
                        </div>
                    '))
                    ->visible(fn ($record) => $record && ! $record->is_customized)
                    ->columnSpanFull(),

                Tabs::make('Article Content')
                    ->tabs([
                        Tabs\Tab::make(__('admin.navigation.resources.help_article.tabs.content'))
                            ->icon(new HtmlString('<i class="fa-light fa-pen-nib fa-fw mr-1"></i>'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('title.cs')
                                            ->label(__('admin.navigation.resources.help_article.fields.title_cs'))
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set, $record) => !($record?->is_customized ?? false) && $set('slug', \Illuminate\Support\Str::slug($state))),
                                        TextInput::make('title.en')
                                            ->label(__('admin.navigation.resources.help_article.fields.title_en'))
                                            ->required(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Textarea::make('excerpt.cs')
                                            ->label(__('admin.navigation.resources.help_article.fields.excerpt_cs'))
                                            ->rows(2)
                                            ->default(null),
                                        Textarea::make('excerpt.en')
                                            ->label(__('admin.navigation.resources.help_article.fields.excerpt_en'))
                                            ->rows(2)
                                            ->default(null),
                                    ]),

                                MarkdownEditor::make('content.cs')
                                    ->label(__('admin.navigation.resources.help_article.fields.content_cs'))
                                    ->required()
                                    ->columnSpanFull(),
                                MarkdownEditor::make('content.en')
                                    ->label(__('admin.navigation.resources.help_article.fields.content_en'))
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make(__('admin.navigation.resources.help_article.tabs.settings'))
                            ->icon(new HtmlString('<i class="fa-light fa-gear fa-fw mr-1"></i>'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('category_id')
                                            ->label(__('admin.navigation.resources.help_article.fields.category'))
                                            ->relationship('category', 'name->cs')
                                            ->searchable()
                                            ->required(),
                                        TextInput::make('slug')
                                            ->label(__('admin.navigation.resources.help_article.fields.slug'))
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('sort_order')
                                            ->label(__('admin.navigation.resources.help_article.fields.sort_order'))
                                            ->required()
                                            ->numeric()
                                            ->default(0),
                                        DateTimePicker::make('published_at')
                                            ->label(__('admin.navigation.resources.help_article.fields.published_at')),
                                        Select::make('audience_roles')
                                            ->label(__('admin.navigation.resources.help_article.fields.audience_roles'))
                                            ->multiple()
                                            ->options(\App\Models\Role::all()->pluck('display_name', 'name'))
                                            ->searchable(),
                                    ]),

                                TextInput::make('search_keywords')
                                    ->label(__('admin.navigation.resources.help_article.fields.search_keywords'))
                                    ->placeholder('tag1, tag2, synonymum...')
                                    ->helperText('Oddělujte čárkou.')
                                    ->columnSpanFull(),

                                Grid::make(3)
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label(__('admin.navigation.resources.help_article.fields.is_published'))
                                            ->default(true)
                                            ->required(),
                                        Toggle::make('is_featured')
                                            ->label(__('admin.navigation.resources.help_article.fields.is_featured'))
                                            ->default(false)
                                            ->required(),
                                        Toggle::make('is_customized')
                                            ->label(__('admin.navigation.resources.help_article.fields.is_customized'))
                                            ->helperText('Pokud je zapnuto, obsah nebude přepsán při seedování.')
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('admin.navigation.resources.help_article.tabs.metadata'))
                            ->icon(new HtmlString('<i class="fa-light fa-code fa-fw mr-1"></i>'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Textarea::make('metadata.cs')
                                            ->label(__('admin.navigation.resources.help_article.fields.metadata') . ' (CS)')
                                            ->rows(8)
                                            ->helperText('Strukturovaná metadata článku v češtině (JSON).'),
                                        Textarea::make('metadata.en')
                                            ->label(__('admin.navigation.resources.help_article.fields.metadata') . ' (EN)')
                                            ->rows(8)
                                            ->helperText('Strukturovaná metadata článku v angličtině (JSON).'),
                                    ]),

                                TextInput::make('source_hash')
                                    ->label('Hash zdroje')
                                    ->disabled()
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

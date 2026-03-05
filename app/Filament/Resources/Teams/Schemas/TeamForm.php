<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Support\IconHelper;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(new HtmlString(IconHelper::render(IconHelper::INFO).' '.__('admin.navigation.resources.team.tabs.general')))
                    ->schema([
                        TextInput::make('name.cs')
                            ->label('Název týmu (CZ)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        TextInput::make('name.en')
                            ->label('Team name (EN)')
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label(__('admin.navigation.resources.team.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('category')
                            ->label(__('admin.navigation.resources.team.fields.category'))
                            ->options([
                                'senior' => __('teams.categories.senior'),
                                'youth' => __('teams.categories.youth'),
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Section::make(new HtmlString(IconHelper::render(IconHelper::LIST_ICON).' '.__('admin.navigation.resources.team.fields.description')))
                    ->schema([
                        Textarea::make('description.cs')
                            ->label('Popis týmu (CZ)')
                            ->rows(5)
                            ->default(null)
                            ->columnSpanFull(),
                        Textarea::make('description.en')
                            ->label('Team description (EN)')
                            ->rows(5)
                            ->default(null)
                            ->columnSpanFull(),
                    ]),

                Section::make(new HtmlString(IconHelper::render(IconHelper::STAT_SOURCES).' '.'Externí synchronizace (cz.basketball)'))
                    ->schema([
                        Repeater::make('externalMappings')
                            ->relationship('externalMappings')
                            ->schema([
                                TextInput::make('source_key')
                                    ->label('Zdroj')
                                    ->default('czbasketball')
                                    ->required()
                                    ->readOnly(),
                                TextInput::make('external_team_id')
                                    ->label('Externí ID týmu')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Např. 7738'),
                                TextInput::make('base_team_url')
                                    ->label('Základní URL týmu')
                                    ->required()
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Např. https://cz.basketball/tym/7738'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Přidat externí zdroj')
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['source_key'] ?? null),
                    ]),
            ]);
    }
}

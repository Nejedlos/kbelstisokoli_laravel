<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Support\IconHelper;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(new HtmlString(IconHelper::render(IconHelper::INFO) . ' ' . __('admin.navigation.resources.team.tabs.general')))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.navigation.resources.team.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
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

                Section::make(new HtmlString(IconHelper::render(IconHelper::LIST) . ' ' . __('admin.navigation.resources.team.fields.description')))
                    ->schema([
                        Textarea::make('description')
                            ->label(__('admin.navigation.resources.team.fields.description'))
                            ->rows(5)
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

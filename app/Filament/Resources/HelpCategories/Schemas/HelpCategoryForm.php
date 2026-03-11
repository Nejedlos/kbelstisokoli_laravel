<?php

namespace App\Filament\Resources\HelpCategories\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class HelpCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Placeholder::make('is_customized_notice')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="flex items-center gap-3 p-4 bg-primary-50 border border-primary-100 rounded-xl text-primary-800">
                                    <i class="fa-light fa-circle-info text-xl text-primary-500"></i>
                                    <div>
                                        <p class="font-bold">Systémově spravovaný obsah</p>
                                        <p class="text-sm opacity-90">Tato kategorie je synchronizována ze zdrojových souborů. Pokud ji upravíte, bude označena jako "Uživatelsky upraveno" a přestane se automaticky aktualizovat.</p>
                                    </div>
                                </div>
                            '))
                            ->visible(fn ($record) => $record && ! $record->is_customized)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Select::make('parent_id')
                                    ->label(__('admin.navigation.resources.help_category.fields.parent'))
                                    ->relationship('parent', 'name->cs')
                                    ->searchable()
                                    ->placeholder('Vyberte nadřazenou kategorii...')
                                    ->default(null),

                                TextInput::make('slug')
                                    ->label(__('admin.navigation.resources.help_category.fields.slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('name.cs')
                                    ->label(__('admin.navigation.resources.help_category.fields.name_cs'))
                                    ->required(),
                                TextInput::make('name.en')
                                    ->label(__('admin.navigation.resources.help_category.fields.name_en'))
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Textarea::make('description.cs')
                                    ->label(__('admin.navigation.resources.help_category.fields.description_cs'))
                                    ->rows(3)
                                    ->default(null),
                                Textarea::make('description.en')
                                    ->label(__('admin.navigation.resources.help_category.fields.description_en'))
                                    ->rows(3)
                                    ->default(null),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('icon')
                                    ->label(__('admin.navigation.resources.help_category.fields.icon'))
                                    ->placeholder('fa-light fa-...')
                                    ->helperText('Název Font Awesome Light ikony.')
                                    ->default(null),
                                TextInput::make('color')
                                    ->label(__('admin.navigation.resources.help_category.fields.color'))
                                    ->placeholder('#rrggbb')
                                    ->default(null),
                                TextInput::make('sort_order')
                                    ->label(__('admin.navigation.resources.help_category.fields.sort_order'))
                                    ->required()
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Select::make('audience_roles')
                            ->label(__('admin.navigation.resources.help_category.fields.audience_roles'))
                            ->multiple()
                            ->options(\App\Models\Role::all()->pluck('display_name', 'name'))
                            ->searchable()
                            ->default(null),

                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('admin.navigation.resources.help_category.fields.is_active'))
                                    ->default(true)
                                    ->required(),
                                Toggle::make('is_featured')
                                    ->label(__('admin.navigation.resources.help_category.fields.is_featured'))
                                    ->default(false)
                                    ->required(),
                                Toggle::make('is_customized')
                                    ->label(__('admin.navigation.resources.help_category.fields.is_customized'))
                                    ->helperText('Pokud je zapnuto, obsah nebude přepsán při seedování.')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Systémové informace')
                    ->collapsed()
                    ->schema([
                        TextInput::make('source_hash')
                            ->label(__('admin.navigation.resources.help_category.fields.source_hash'))
                            ->disabled()
                            ->default(null),
                    ]),
            ]);
    }
}

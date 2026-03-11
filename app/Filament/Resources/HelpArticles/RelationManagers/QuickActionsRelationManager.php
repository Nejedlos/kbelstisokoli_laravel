<?php

namespace App\Filament\Resources\HelpArticles\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class QuickActionsRelationManager extends RelationManager
{
    protected static string $relationship = 'quickActions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('title.cs')
                            ->label(__('admin.navigation.resources.help_quick_action.fields.title_cs'))
                            ->required(),
                        TextInput::make('title.en')
                            ->label(__('admin.navigation.resources.help_quick_action.fields.title_en'))
                            ->required(),
                    ]),
                Grid::make(2)
                    ->schema([
                        Textarea::make('description.cs')
                            ->label(__('admin.navigation.resources.help_quick_action.fields.description_cs'))
                            ->rows(2)
                            ->default(null),
                        Textarea::make('description.en')
                            ->label(__('admin.navigation.resources.help_quick_action.fields.description_en'))
                            ->rows(2)
                            ->default(null),
                    ]),
                Grid::make(3)
                    ->schema([
                        TextInput::make('url')
                            ->label(__('admin.navigation.resources.help_quick_action.fields.url'))
                            ->placeholder('admin.resource.index nebo /admin/...')
                            ->required(),
                        TextInput::make('icon')
                            ->label(__('admin.navigation.resources.help_quick_action.fields.icon'))
                            ->placeholder('fa-light fa-...')
                            ->default(null),
                        TextInput::make('sort_order')
                            ->label(__('admin.navigation.resources.help_quick_action.fields.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.navigation.resources.help_quick_action.fields.title_cs'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('title->cs', 'like', "%{$search}%")
                            ->orWhere('title->en', 'like', "%{$search}%");
                    }),
                TextColumn::make('icon')
                    ->label(__('admin.navigation.resources.help_quick_action.fields.icon'))
                    ->formatStateUsing(fn (?string $state): ?HtmlString => $state ? new HtmlString("<i class='{$state} fa-fw'></i>") : null)
                    ->alignCenter(),
                TextColumn::make('url')
                    ->label(__('admin.navigation.resources.help_quick_action.fields.url'))
                    ->limit(30),
                TextColumn::make('sort_order')
                    ->label(__('admin.navigation.resources.help_quick_action.fields.sort_order'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

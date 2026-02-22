<?php

namespace App\Filament\Resources\PostCategories;

use App\Filament\Resources\PostCategories\Pages\ManagePostCategories;
use App\Models\PostCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.content');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::CATEGORIES);
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.post_category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.post_category.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Název')
                    ->required(),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->helperText('Použijte krátký, srozumitelný identifikátor bez diakritiky (např. "novinky-klubu").')
                    ->required()
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->label('Popis')
                    ->columnSpanFull(),

                TextInput::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => ManagePostCategories::route('/'),
        ];
    }
}

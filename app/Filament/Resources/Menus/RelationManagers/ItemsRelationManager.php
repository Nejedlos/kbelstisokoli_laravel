<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('label')
                    ->label('Popisek')
                    ->required(),

                Select::make('parent_id')
                    ->label('Nadřazená položka')
                    ->relationship('parent', 'label')
                    ->searchable()
                    ->preload(),

                TextInput::make('route_name')
                    ->label('Route název')
                    ->placeholder('např. public.home')
                    ->helperText('Doporučeno. Pokud je vyplněno, použije se route. URL slouží jako fallback.'),

                TextInput::make('url')
                    ->label('URL (volitelné)')
                    ->placeholder('/kontakt'),

                Select::make('target')
                    ->label('Cíl odkazu')
                    ->options([
                        '_self' => 'Stejné okno',
                        '_blank' => 'Nové okno',
                    ])->default('_self'),

                TextInput::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->default(0),

                Toggle::make('is_visible')
                    ->label('Viditelné?')
                    ->default(true),
            ])->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('label')->label('Popisek')->sortable()->searchable(),
                TextColumn::make('route_name')->label('Route')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')->label('URL')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('parent.label')->label('Nadřazená'),
                IconColumn::make('is_visible')->label('Viditelné')->boolean(),
                TextColumn::make('sort_order')->label('Pořadí')->sortable(),
            ]);
    }
}

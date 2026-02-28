<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('Náhled')
                    ->circular(),

                TextColumn::make('title')
                    ->label('Titulek')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->slug),

                TextColumn::make('category.name')
                    ->label('Kategorie')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Koncept',
                        'published' => 'Publikováno',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('publish_at')
                    ->label('Publikováno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                IconColumn::make('is_visible')
                    ->label('Viditelné')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Kategorie')
                    ->relationship('category', 'name'),

                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'draft' => 'Koncept',
                        'published' => 'Publikováno',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

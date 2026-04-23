<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                SpatieMediaLibraryImageColumn::make('featured_image')
                    ->label('Náhled')
                    ->collection('featured_image')
                    ->circular(),

                TextColumn::make('title')
                    ->label('Titulek')
                    ->searchable(query: function ($query, string $search): \Illuminate\Database\Eloquent\Builder {
                        $locale = app()->getLocale();
                        return $query->where("title->{$locale}", 'LIKE', "%{$search}%");
                    })
                    ->description(fn ($record) => $record->slug),

                TextColumn::make('category.name')
                    ->label('Kategorie')
                    ->searchable(query: function ($query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('category', function ($q) use ($search) {
                            $locale = app()->getLocale();

                            return $q->where("name->{$locale}", 'LIKE', "%{$search}%");
                        });
                    }),

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
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

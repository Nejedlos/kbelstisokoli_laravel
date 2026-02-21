<?php

namespace App\Filament\Resources\Galleries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;

class GalleriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Název')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->slug),

                TextColumn::make('variant')
                    ->label('Styl')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'grid' => 'Mřížka',
                        'masonry' => 'Mozaika',
                        default => $state,
                    })
                    ->color('gray'),

                IconColumn::make('is_public')
                    ->label('Veřejná')
                    ->boolean(),

                IconColumn::make('is_visible')
                    ->label('Viditelná')
                    ->boolean(),

                TextColumn::make('media_assets_count')
                    ->label('Položek')
                    ->counts('mediaAssets')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Publikováno')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_public')
                    ->label('Veřejnost'),
                TernaryFilter::make('is_visible')
                    ->label('Viditelnost'),
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

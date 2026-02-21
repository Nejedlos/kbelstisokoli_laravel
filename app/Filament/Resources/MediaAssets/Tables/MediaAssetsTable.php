<?php

namespace App\Filament\Resources\MediaAssets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\SelectFilter;

class MediaAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('file')
                    ->label('Náhled')
                    ->collection('default')
                    ->conversion('thumb')
                    ->circular(),

                TextColumn::make('title')
                    ->label('Název')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->alt_text),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'image' => 'Obrázek',
                        'document' => 'Dokument',
                        'video' => 'Video',
                        default => $state,
                    })
                    ->color('gray'),

                TextColumn::make('access_level')
                    ->label('Přístup')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'public' => 'success',
                        'member' => 'info',
                        'private' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'public' => 'Veřejné',
                        'member' => 'Členové',
                        'private' => 'Soukromé',
                        default => $state,
                    }),

                IconColumn::make('is_public')
                    ->label('V knihovně')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Nahráno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ média')
                    ->options([
                        'image' => 'Obrázek',
                        'document' => 'Dokument',
                        'video' => 'Video',
                    ]),
                SelectFilter::make('access_level')
                    ->label('Úroveň přístupu')
                    ->options([
                        'public' => 'Veřejné',
                        'member' => 'Členové',
                        'private' => 'Soukromé',
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

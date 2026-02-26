<?php

namespace App\Filament\Resources\PhotoPools\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaAssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'mediaAssets';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.resources.photo_pool.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('default')
                    ->label(__('admin.navigation.resources.photo_pool.fields.photos'))
                    ->collection('default')
                    ->conversion('thumb')
                    ->square(),
                TextColumn::make('title')
                    ->label(__('user.fields.display_name'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Nahráno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([
                // Přidávání probíhá přes záložku "Fotografie" ve formuláři
            ])
            ->actions([
                EditAction::make()
                    ->label(__('user.actions.edit')),
                DetachAction::make()
                    ->label(__('admin.navigation.resources.photo_pool.actions.detach')),
                DeleteAction::make()
                    ->label(__('user.actions.delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label(__('admin.navigation.resources.photo_pool.actions.detach')),
                    DeleteBulkAction::make()
                        ->label(__('user.actions.delete_selected')),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}

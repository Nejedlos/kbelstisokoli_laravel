<?php

namespace App\Filament\Resources\HelpArticles\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RelatedArticlesRelationManager extends RelationManager
{
    protected static string $relationship = 'relatedArticles';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Ponecháme prázdné nebo základní, protože primárně používáme Attach
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title->cs')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.resources.help_article.fields.title_cs'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('title->cs', 'like', "%{$search}%")
                            ->orWhere('title->en', 'like', "%{$search}%");
                    }),
                TextColumn::make('category.name')
                    ->label(__('admin.resources.help_article.fields.category'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '-')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}

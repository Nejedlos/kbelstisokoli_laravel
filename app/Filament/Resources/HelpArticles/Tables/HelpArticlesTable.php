<?php

namespace App\Filament\Resources\HelpArticles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HelpArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.resources.help_article.fields.title_cs'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('title', 'like', "%{$search}%");
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('title', $direction);
                    }),

                TextColumn::make('category.name')
                    ->label(__('admin.resources.help_article.fields.category'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('audience_roles')
                    ->label(__('admin.resources.help_article.fields.audience_roles'))
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label(__('admin.resources.help_article.fields.sort_order'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label(__('admin.resources.help_article.fields.is_published'))
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('is_featured')
                    ->label(__('admin.resources.help_article.fields.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),

                IconColumn::make('is_customized')
                    ->label(__('admin.resources.help_article.fields.is_customized'))
                    ->boolean()
                    ->toggleable()
                    ->alignCenter(),

                TextColumn::make('published_at')
                    ->label(__('admin.resources.help_article.fields.published_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Upraveno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('admin.resources.help_article.fields.category'))
                    ->relationship('category', 'name')
                    ->searchable(),
                SelectFilter::make('is_published')
                    ->label(__('admin.resources.help_article.fields.is_published'))
                    ->options([
                        '1' => 'Publikováno',
                        '0' => 'Koncept',
                    ]),
                SelectFilter::make('audience_roles')
                    ->label(__('admin.resources.help_article.fields.audience_roles'))
                    ->multiple()
                    ->options([
                        'admin' => 'Admin',
                        'coach' => 'Trenér',
                        'editor' => 'Editor',
                        'player' => 'Hráč',
                        'parent' => 'Rodič',
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['values'])) {
                            return $query;
                        }
                        return $query->where(function ($q) use ($data) {
                            foreach ($data['values'] as $value) {
                                $q->orWhere('audience_roles', 'LIKE', '%"' . $value . '"%');
                            }
                        });
                    }),
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

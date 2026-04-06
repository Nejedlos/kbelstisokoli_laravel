<?php

namespace App\Filament\Resources\HelpCategories\Tables;

use App\Support\Icons\AppIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class HelpCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.help_category.fields.name_cs'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('name->cs', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%");
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('name->cs', $direction);
                    }),

                TextColumn::make('slug')
                    ->label(__('admin.resources.help_category.fields.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('parent.name')
                    ->label(__('admin.resources.help_category.fields.parent'))
                    ->formatStateUsing(fn ($state) => $state['cs'] ?? '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('icon')
                    ->label(__('admin.resources.help_category.fields.icon'))
                    ->formatStateUsing(fn (?string $state): ?HtmlString => $state ? new HtmlString("<i class='{$state} fa-fw'></i>") : null)
                    ->alignCenter(),

                TextColumn::make('audience_roles')
                    ->label(__('admin.resources.help_category.fields.audience_roles'))
                    ->badge()
                    ->separator(',')
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label(__('admin.resources.help_category.fields.sort_order'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.resources.help_category.fields.is_active'))
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('is_featured')
                    ->label(__('admin.resources.help_category.fields.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),

                IconColumn::make('is_customized')
                    ->label(__('admin.resources.help_category.fields.is_customized'))
                    ->boolean()
                    ->toggleable()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Upraveno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('admin.resources.help_category.fields.is_active'))
                    ->options([
                        '1' => 'Aktivní',
                        '0' => 'Neaktivní',
                    ]),
                SelectFilter::make('audience_roles')
                    ->label(__('admin.resources.help_category.fields.audience_roles'))
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
                                $q->orWhereJsonContains('audience_roles', $value);
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

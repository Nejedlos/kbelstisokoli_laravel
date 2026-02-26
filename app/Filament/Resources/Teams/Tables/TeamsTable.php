<?php

namespace App\Filament\Resources\Teams\Tables;

use App\Support\IconHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.navigation.resources.team.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->label(__('admin.navigation.resources.team.fields.category'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'senior' => __('teams.categories.senior'),
                        'youth' => __('teams.categories.youth'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'senior' => 'primary',
                        'youth' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('coaches_count')
                    ->label(new HtmlString(IconHelper::render(IconHelper::USERS_GROUP) . ' ' . __('admin.navigation.resources.team.fields.coaches_count')))
                    ->counts('coaches')
                    ->sortable(),
                TextColumn::make('players_count')
                    ->label(new HtmlString(IconHelper::render(IconHelper::USERS) . ' ' . __('admin.navigation.resources.team.fields.players_count')))
                    ->counts('players')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view_public')
                    ->label(__('admin.navigation.resources.team.actions.view_public'))
                    ->icon(IconHelper::get(IconHelper::GLOBE))
                    ->url(fn ($record) => route('public.teams.show', $record->slug))
                    ->openUrlInNewTab()
                    ->color('gray'),
                EditAction::make()
                    ->label(__('user.actions.edit'))
                    ->icon(IconHelper::get(IconHelper::EDIT)),
                ReplicateAction::make()
                    ->label(__('user.actions.replicate'))
                    ->icon(IconHelper::get(IconHelper::COPY))
                    ->color('warning'),
                DeleteAction::make()
                    ->label(__('user.actions.delete'))
                    ->icon(IconHelper::get(IconHelper::TRASH)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('user.actions.delete_selected')),
                ]),
            ]);
    }
}

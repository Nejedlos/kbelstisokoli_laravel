<?php

namespace App\Filament\Resources\Teams\Tables;

use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.team.fields.name'))
                    ->searchable(query: function ($query, string $search): \Illuminate\Database\Eloquent\Builder {
                        $locale = app()->getLocale();
                        return $query->where("name->{$locale}", 'LIKE', "%{$search}%");
                    }),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->label(__('admin.resources.team.fields.category'))
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
                TextColumn::make('active_coaches_count')
                    ->label(new HtmlString(IconHelper::render(IconHelper::USERS_GROUP).' '.__('admin.resources.team.fields.coaches_count')))
                    ->counts('activeCoaches')
                    ->sortable(),
                TextColumn::make('active_players_count')
                    ->label(new HtmlString(IconHelper::render(IconHelper::USERS).' '.__('admin.resources.team.fields.players_count')))
                    ->counts('activePlayers')
                    ->sortable(),
                TextColumn::make('roster_players_count')
                    ->label(new HtmlString(IconHelper::render(IconHelper::BASKETBALL).' '.__('admin.resources.team.fields.roster_count')))
                    ->counts('rosterPlayers')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.resources.team.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.resources.team.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_public')
                        ->label(__('admin.resources.team.actions.view_public'))
                        ->icon(IconHelper::render(IconHelper::GLOBE))
                        ->url(fn ($record) => route('public.teams.show', $record->slug))
                        ->openUrlInNewTab()
                        ->color('gray'),
                    EditAction::make()
                        ->label(__('user.actions.edit'))
                        ->icon(IconHelper::render(IconHelper::EDIT)),
                    ReplicateAction::make()
                        ->label(__('user.actions.replicate'))
                        ->icon(IconHelper::render(IconHelper::COPY))
                        ->color('warning'),
                    DeleteAction::make()
                        ->label(__('user.actions.delete'))
                        ->icon(IconHelper::render(IconHelper::TRASH)),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('user.actions.delete_selected')),
                ]),
            ]);
    }
}

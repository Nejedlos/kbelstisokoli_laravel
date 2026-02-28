<?php

namespace App\Filament\Resources\Trainings\Tables;

use App\Models\Training;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->recordClasses(fn (Training $record) => $record->starts_at->isFuture() ? 'bg-success-50/70 dark:bg-success-900/10' : 'bg-gray-50/50 dark:bg-white/5')
            ->columns([
                TextColumn::make('teams.name')
                    ->label(__('admin.navigation.resources.team.plural_label'))
                    ->badge()
                    ->state(fn (Training $record) => $record->teams->reject(fn ($team) => $team->category === 'all')->pluck('name'))
                    ->searchable(),
                TextColumn::make('mismatches_count')
                    ->label(__('fields.mismatches'))
                    ->counts('mismatches')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                    ->sortable(),
                TextColumn::make('location')
                    ->label(__('fields.location'))
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->label(__('fields.starts_at'))
                    ->dateTime('j.n.Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('fields.ends_at'))
                    ->dateTime('j.n.Y H:i')
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

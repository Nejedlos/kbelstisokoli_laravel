<?php

namespace App\Filament\Resources\ClubCompetitions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClubCompetitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.club_competition.fields.name'))
                    ->searchable(query: function ($query, string $search): \Illuminate\Database\Eloquent\Builder {
                        $locale = app()->getLocale();
                        return $query->where("name->{$locale}", 'LIKE', "%{$search}%");
                    })
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label(__('admin.resources.club_competition.fields.season'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin.resources.club_competition.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'gray',
                        'archived' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Probíhá',
                        'completed' => 'Ukončeno',
                        'archived' => 'Archivováno',
                        default => $state,
                    }),
                TextColumn::make('entries_count')
                    ->label(__('admin.resources.club_competition.fields.entries_count'))
                    ->counts('entries'),
                IconColumn::make('is_public')
                    ->label(__('admin.resources.club_competition.fields.is_public'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Poslední aktivita')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('season')
                    ->label('Sezóna')
                    ->relationship('season', 'name'),
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'active' => 'Probíhá',
                        'completed' => 'Ukončeno',
                        'archived' => 'Archivováno',
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

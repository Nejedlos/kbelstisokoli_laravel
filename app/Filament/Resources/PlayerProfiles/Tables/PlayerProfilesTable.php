<?php

namespace App\Filament\Resources\PlayerProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PlayerProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user.externalMappings']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Jméno')
                    ->formatStateUsing(fn ($state, $record) => new HtmlString(
                        ($record->user?->externalMappings->isNotEmpty()
                            ? '<i class="fa-light fa-cloud-arrow-down fa-fw text-info mr-1" title="Synchronizováno z externího zdroje"></i> '
                            : '') . e($state)
                    ))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jersey_number')
                    ->label('Dres #')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('position')
                    ->label('Pozice')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('teams.name')
                    ->label('Týmy')
                    ->badge()
                    ->state(fn ($record) => $record->teams->reject(fn ($team) => $team->category === 'all')->pluck('name'))
                    ->separator(','),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('teams')
                    ->label('Tým')
                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Aktivní status'),
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

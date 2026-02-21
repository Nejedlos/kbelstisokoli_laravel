<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Jméno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('info')
                    ->separator(','),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('playerProfile')
                    ->label('Hráč')
                    ->state(fn ($record) => $record->playerProfile !== null)
                    ->boolean()
                    ->trueIcon('heroicon-o-user-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('last_login_at')
                    ->label('Poslední přihlášení')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Pouze aktivní'),
                TernaryFilter::make('player_profile_exists')
                    ->label('Má hráčský profil')
                    ->placeholder('Všichni')
                    ->trueQuery(fn ($query) => $query->has('playerProfile'))
                    ->falseQuery(fn ($query) => $query->doesntHave('playerProfile')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Aktivovat vybrané')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->authorize(fn () => auth()->user()?->can('manage_users'))
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Deaktivovat vybrané')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->authorize(fn () => auth()->user()?->can('manage_users'))
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }
}

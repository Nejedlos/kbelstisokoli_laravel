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

use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Password;
use App\Notifications\UserInvitationNotification;
use Filament\Notifications\Notification as FilamentNotification;

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
                IconColumn::make('two_factor_secret')
                    ->label('2FA')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->sortable(),
                IconColumn::make('onboarding_completed_at')
                    ->label('Aktivováno')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-clock')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
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
                TernaryFilter::make('two_factor')
                    ->label('Aktivní 2FA')
                    ->trueQuery(fn ($query) => $query->whereNotNull('two_factor_secret'))
                    ->falseQuery(fn ($query) => $query->whereNull('two_factor_secret')),
                TernaryFilter::make('onboarding')
                    ->label('Dokončený onboarding')
                    ->trueQuery(fn ($query) => $query->whereNotNull('onboarding_completed_at'))
                    ->falseQuery(fn ($query) => $query->whereNull('onboarding_completed_at')),
                TernaryFilter::make('player_profile_exists')
                    ->label('Má hráčský profil')
                    ->placeholder('Všichni')
                    ->trueQuery(fn ($query) => $query->has('playerProfile'))
                    ->falseQuery(fn ($query) => $query->doesntHave('playerProfile')),
            ])
            ->recordActions([
                Action::make('sendInvitation')
                    ->label('Poslat pozvánku')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Odeslat pozvánku k aktivaci účtu')
                    ->modalDescription('Uživateli bude zaslán e-mail s odkazem pro nastavení hesla. Tato akce je vhodná pro nově vytvořené účty.')
                    ->action(function ($record) {
                        $token = Password::createToken($record);
                        $record->notify(new UserInvitationNotification($token));

                        FilamentNotification::make()
                            ->title('Pozvánka byla odeslána')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->is_active && !$record->onboarding_completed_at),
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

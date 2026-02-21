<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

use Illuminate\Support\Facades\Password;
use App\Notifications\UserInvitationNotification;
use Filament\Notifications\Notification as FilamentNotification;
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
                IconColumn::make('two_factor_confirmed_at')
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
                TernaryFilter::make('two_factor_confirmed')
                    ->label('Stav 2FA')
                    ->placeholder('Všichni')
                    ->trueLabel('Zabezpečeno (Potvrzeno)')
                    ->falseLabel('Nezabezpečeno / Čeká')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('two_factor_confirmed_at'),
                        false: fn ($query) => $query->whereNull('two_factor_confirmed_at'),
                    ),
                TernaryFilter::make('onboarding')
                    ->label('Dokončený onboarding')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('onboarding_completed_at'),
                        false: fn ($query) => $query->whereNull('onboarding_completed_at'),
                    ),
                TernaryFilter::make('player_profile_exists')
                    ->label('Má hráčský profil')
                    ->placeholder('Všichni')
                    ->queries(
                        true: fn ($query) => $query->has('playerProfile'),
                        false: fn ($query) => $query->doesntHave('playerProfile'),
                    ),
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
                Action::make('disable2fa')
                    ->label('Resetovat 2FA')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Resetovat dvoufázové ověření?')
                    ->modalDescription('Uživateli bude zrušeno nastavené 2FA. Tuto akci proveďte pouze pokud uživatel ztratil přístup k autentikátoru. Po resetu bude uživatel při příštím vstupu do adminu vyzván k novému nastavení.')
                    ->authorize(fn ($record) => auth()->user()?->can('manage_users'))
                    ->action(function ($record) {
                        $record->update([
                            'two_factor_secret' => null,
                            'two_factor_recovery_codes' => null,
                            'two_factor_confirmed_at' => null,
                        ]);

                        FilamentNotification::make()
                            ->title('2FA bylo resetováno')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->two_factor_secret !== null),
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
                    BulkAction::make('reset2fa')
                        ->label('Resetovat 2FA')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Resetovat 2FA u vybraných uživatelů?')
                        ->modalDescription('Tato akce zruší nastavení 2FA u všech vybraných uživatelů. Budou se muset znovu zajistit při příštím vstupu do adminu.')
                        ->authorize(fn () => auth()->user()?->can('manage_users'))
                        ->action(fn (Collection $records) => $records->each->update([
                            'two_factor_secret' => null,
                            'two_factor_recovery_codes' => null,
                            'two_factor_confirmed_at' => null,
                        ])),
                ]),
            ]);
    }
}

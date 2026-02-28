<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Gender;
use App\Enums\MembershipStatus;
use App\Enums\MembershipType;
use App\Notifications\UserInvitationNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->label(__('user.fields.avatar'))
                    ->collection('avatar')
                    ->conversion('thumb')
                    ->defaultImageUrl(asset('images/default-avatar-thumb.webp'))
                    ->circular()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label(__('user.fields.first_name').' '.__('user.fields.last_name'))
                    ->description(fn ($record) => $record->email)
                    ->searchable(['name', 'email', 'first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('club_member_id')
                    ->label(__('user.fields.club_member_id'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->toggleable(),
                TextColumn::make('payment_vs')
                    ->label(__('user.fields.payment_vs'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('info')
                    ->separator(','),
                TextColumn::make('membership_status')
                    ->label(__('user.fields.membership_status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('playerProfile.jersey_number')
                    ->label('#')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('playerProfile.primaryTeam.name')
                    ->label(__('user.fields.primary_team'))
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('user.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('last_login_at')
                    ->label('Aktivita')
                    ->description(fn ($record) => $record->last_login_at?->diffForHumans() ?? '-')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name'),
                SelectFilter::make('membership_status')
                    ->label(__('user.fields.membership_status'))
                    ->options(MembershipStatus::class),
                SelectFilter::make('membership_type')
                    ->label(__('user.fields.membership_type'))
                    ->options(MembershipType::class),
                SelectFilter::make('preferred_locale')
                    ->label(__('user.fields.preferred_locale'))
                    ->options([
                        'cs' => 'Čeština',
                        'en' => 'English',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Účet aktivní'),
                TernaryFilter::make('two_factor_confirmed')
                    ->label('Stav 2FA')
                    ->placeholder('Všichni')
                    ->trueLabel('2FA potvrzeno')
                    ->falseLabel('2FA neaktivní / čeká')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('two_factor_confirmed_at'),
                        false: fn ($query) => $query->whereNull('two_factor_confirmed_at'),
                    ),
                TernaryFilter::make('has_player_profile')
                    ->label('Hráčský profil')
                    ->queries(
                        true: fn ($query) => $query->has('playerProfile'),
                        false: fn ($query) => $query->doesntHave('playerProfile'),
                    ),
                SelectFilter::make('gender')
                    ->label(__('user.fields.gender'))
                    ->options(Gender::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('sendInvitation')
                        ->label(__('user.actions.send_invitation'))
                        ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PAPER_PLANE))
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $token = Password::createToken($record);
                            $record->notify(new UserInvitationNotification($token));

                            FilamentNotification::make()
                                ->title('Pozvánka byla odeslána')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->is_active && ! $record->onboarding_completed_at),
                    Action::make('impersonate')
                        ->label(__('permissions.impersonate_users'))
                        ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::IMPERSONATE))
                        ->color('warning')
                        ->requiresConfirmation(fn ($record) => __('permissions.impersonate_confirm').$record->name.'?')
                        ->url(fn ($record) => route('admin.impersonate.start', ['userId' => $record->id]))
                        ->visible(fn ($record) => auth()->user()->can('impersonate_users') && auth()->user()->id !== $record->id),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Aktivovat vybrané')
                        ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::ACTIVATE))
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Deaktivovat vybrané')
                        ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::DEACTIVATE))
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Gender;
use App\Enums\MembershipStatus;
use App\Enums\MembershipType;
use App\Mail\TestMail;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use App\Services\Stats\Sync\PlayerSyncService;
use App\Services\Users\UserMergeService;
use App\Support\IconHelper;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\HtmlString;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        \App\Filament\Resources\Users\UserDebug::log('UsersTable: configure start');
        $userModel = new User;
        $userTable = $userModel->getTable();

        $result = $table
            ->striped()
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['externalMappings', 'roles', 'playerProfile.primaryTeam'])
                ->select("users.*")
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('player_photos')
                    ->label('')
                    ->collection('player_photos')
                    ->circular()
                    ->getStateUsing(fn ($record) => $record->getMedia('player_photos')->sortByDesc('id')->first())
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->formatStateUsing(fn ($state, $record) => new HtmlString(
                        ($record->isGhost()
                            ? IconHelper::render(AppIcon::NOT_FOUND, 'fal')->toHtml().' '
                            : '').
                        ($record->externalMappings->isNotEmpty()
                            ? IconHelper::render(AppIcon::STAT_SOURCES, 'fal')->toHtml().' '
                            : '').e($state)
                    ))
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
                TextColumn::make('roles.display_name')
                    ->label(__('user.fields.roles'))
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
                    ->label(__('user.fields.last_activity'))
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
                Filter::make('duplicates')
                    ->label(__('user.filters.duplicates'))
                    ->indicator(__('user.filters.duplicates_indicator'))
                    ->query(fn (Builder $query) => $query->whereIn('name', function ($sub) {
                        $sub->select('name')
                            ->from('users')
                            ->groupBy('name')
                            ->havingRaw('COUNT(*) > 1');
                    })),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('sendTestEmail')
                        ->label(__('admin.email_debug.actions.send_test'))
                        ->icon(new HtmlString('<i class="fa-light fa-paper-plane"></i>'))
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            try {
                                Mail::to($record->email)->send(new TestMail("Toto je testovací e-mail odeslaný z administrace uživatelů pro ověření doručitelnosti na adresu {$record->email}."));

                                FilamentNotification::make()
                                    ->title(__('admin.email_debug.notifications.sent').' ('.$record->email.')')
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                FilamentNotification::make()
                                    ->title(__('admin.email_debug.notifications.error'))
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }),
                    Action::make('sendPasswordReset')
                        ->label(__('admin.resources.user.actions.send_password_reset'))
                        ->icon(new HtmlString('<i class="fa-light fa-key"></i>'))
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            Log::channel('single')->info('DEBUG_MAIL: Admin triggered password reset', [
                                'user_id' => $record->id,
                                'email' => $record->email,
                            ]);

                            $status = Password::broker()->sendResetLink(['email' => $record->email]);

                            Log::channel('single')->info('DEBUG_MAIL: Password reset broker result', [
                                'user_id' => $record->id,
                                'email' => $record->email,
                                'status' => $status,
                                'status_translated' => __($status),
                            ]);

                            if ($status === Password::RESET_LINK_SENT) {
                                FilamentNotification::make()
                                    ->title(__('admin.resources.user.notifications.password_reset_sent'))
                                    ->success()
                                    ->send();
                            } else {
                                FilamentNotification::make()
                                    ->title(__('admin.resources.user.notifications.password_reset_error'))
                                    ->body(__($status))
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => $record->is_active),
                    Action::make('sendInvitation')
                        ->label(__('user.actions.send_invitation'))
                        ->icon(IconHelper::get(IconHelper::PAPER_PLANE))
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $token = Password::broker()->createToken($record);
                            $record->notify(new UserInvitationNotification($token));

                            FilamentNotification::make()
                                ->title(__('user.notifications.invitation_sent'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record->is_active && ! $record->onboarding_completed_at),
                    Action::make('impersonate')
                        ->label(__('user.actions.impersonate'))
                        ->icon(IconHelper::get(IconHelper::IMPERSONATE))
                        ->color('warning')
                        ->requiresConfirmation(fn ($record) => __('user.actions.impersonate_confirm').$record->name.'?')
                        ->url(fn ($record) => route('admin.impersonate.start', ['userId' => $record->id]))
                        ->visible(fn ($record) => auth()->user()->can('impersonate_users') && auth()->user()->id !== $record->id),
                    Action::make('merge')
                        ->label(__('user.actions.merge'))
                        ->icon(new HtmlString('<i class="fa-light fa-object-group"></i>'))
                        ->color('warning')
                        ->form([
                            Select::make('target_user_id')
                                ->label(__('user.actions.merge_target'))
                                ->options(fn ($record) => User::where('id', '!=', $record->id)
                                    ->orderBy('name')
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->helperText(__('user.actions.merge_helper')),
                        ])
                        ->requiresConfirmation()
                        ->modalHeading(__('user.actions.merge_title'))
                        ->modalDescription(__('user.actions.merge_desc'))
                        ->modalSubmitActionLabel(__('user.actions.merge_submit'))
                        ->visible(fn () => auth()->user()?->can('manage_users') && auth()->user()?->hasRole('admin'))
                        ->action(function ($record, array $data, UserMergeService $service) {
                            $targetUser = User::findOrFail($data['target_user_id']);
                            $service->merge($record, $targetUser);

                            FilamentNotification::make()
                                ->title(__('user.notifications.merged'))
                                ->success()
                                ->send();
                        }),
                    Action::make('syncExternal')
                        ->label(__('user.actions.sync_external'))
                        ->icon(new HtmlString('<i class="fa-light fa-arrows-rotate"></i>'))
                        ->color('info')
                        ->visible(fn ($record) => auth()->user()?->can('manage_users') && $record->externalMappings->where('source_key', 'czbasketball')->isNotEmpty())
                        ->action(function ($record, PlayerSyncService $service) {
                            $result = $service->syncPlayer($record);

                            if ($result) {
                                FilamentNotification::make()
                                    ->title(__('user.notifications.sync_success'))
                                    ->success()
                                    ->send();
                            } else {
                                FilamentNotification::make()
                                    ->title(__('user.notifications.sync_failed'))
                                    ->body(__('user.notifications.sync_failed_body'))
                                    ->danger()
                                    ->send();
                            }
                        }),
                    EditAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('mergeAllGhosts')
                    ->label(__('user.actions.merge_ghosts'))
                    ->icon(new HtmlString('<i class="fa-light fa-object-group"></i>'))
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription(__('user.actions.merge_ghosts_desc'))
                    ->visible(fn () => auth()->user()?->hasRole('admin'))
                    ->action(function (UserMergeService $service) {
                        // Musíme pracovat s query, abychom se vyhnuli problémům s pamětí u velkého množství uživatelů
                        // Ale pro začátek stačí filter na kolekci, pokud jich není tisíce
                        $ghosts = User::all()->filter(fn ($u) => $u->isGhost());
                        $mergedCount = 0;

                        foreach ($ghosts as $ghost) {
                            $target = $service->findMergeTarget($ghost);
                            if ($target) {
                                $service->merge($ghost, $target);
                                $mergedCount++;
                            }
                        }

                        if ($mergedCount > 0) {
                            Notification::make()
                                ->success()
                                ->title(__('user.notifications.bulk_merge_success'))
                                ->body(__('user.notifications.bulk_merge_body', ['count' => $mergedCount]))
                                ->send();
                        } else {
                            Notification::make()
                                ->info()
                                ->title(__('user.notifications.bulk_merge_none'))
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mergeAutomatically')
                        ->label(__('user.actions.merge_bulk_submit'))
                        ->icon(new HtmlString('<i class="fa-light fa-object-group"></i>'))
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('user.actions.merge_bulk_title'))
                        ->modalDescription(__('user.actions.merge_bulk_desc'))
                        ->modalSubmitActionLabel(__('user.actions.merge_bulk_submit'))
                        ->visible(fn () => auth()->user()?->hasRole('admin'))
                        ->action(function (Collection $records, UserMergeService $service) {
                            $mergedCount = 0;
                            $skippedCount = 0;

                            foreach ($records as $record) {
                                if (! $record->isGhost()) {
                                    $skippedCount++;

                                    continue;
                                }

                                $targetUser = $service->findMergeTarget($record);

                                if ($targetUser) {
                                    $service->merge($record, $targetUser);
                                    $mergedCount++;
                                } else {
                                    $skippedCount++;
                                }
                            }

                            FilamentNotification::make()
                                ->title(__('user.notifications.bulk_merge_success'))
                                ->body(__('user.notifications.bulk_merge_summary', ['merged' => $mergedCount, 'skipped' => $skippedCount]))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('syncExternalBulk')
                        ->label(__('user.actions.sync_external'))
                        ->icon(new HtmlString('<i class="fa-light fa-arrows-rotate"></i>'))
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records, PlayerSyncService $service) {
                            $successCount = 0;
                            foreach ($records as $record) {
                                if ($service->syncPlayer($record)) {
                                    $successCount++;
                                }
                            }

                            FilamentNotification::make()
                                ->title(__('user.notifications.bulk_sync_success'))
                                ->body(__('user.notifications.bulk_sync_body', ['success' => $successCount, 'total' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('activate')
                        ->label(__('user.actions.activate_selected'))
                        ->icon(IconHelper::get(IconHelper::ACTIVATE))
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label(__('user.actions.deactivate_selected'))
                        ->icon(IconHelper::get(IconHelper::DEACTIVATE))
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ]);

        \App\Filament\Resources\Users\UserDebug::log('UsersTable: configure end');

        return $result;
    }
}

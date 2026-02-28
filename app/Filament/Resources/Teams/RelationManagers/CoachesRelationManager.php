<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoachesRelationManager extends RelationManager
{
    protected static string $relationship = 'coaches';

    protected function modifyQueryUsing(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('users.is_active', true);
    }

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.resources.team.fields.coaches');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('user.fields.full_name'))
                    ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pivot.email')
                    ->label(__('admin.navigation.resources.team.fields.coach_email'))
                    ->placeholder(fn ($record) => $record->email)
                    ->searchable(),
                TextColumn::make('pivot.phone')
                    ->label(__('admin.navigation.resources.team.fields.coach_phone'))
                    ->placeholder(fn ($record) => (string) $record->phone)
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.attach_coach'))
                    ->icon(IconHelper::get(IconHelper::PLUS))
                    ->visible(fn (): bool => auth()->user()->can('manage_rosters'))
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if (! $state) {
                                    return;
                                }

                                $user = \App\Models\User::find($state);

                                if ($user) {
                                    $set('email', $user->email);
                                    $set('phone', (string) $user->phone);
                                }
                            }),
                        TextInput::make('email')
                            ->label(__('admin.navigation.resources.team.fields.coach_email'))
                            ->helperText(__('admin.navigation.resources.team.fields.coach_email_help'))
                            ->email(),
                        TextInput::make('phone')
                            ->label(__('admin.navigation.resources.team.fields.coach_phone'))
                            ->helperText(__('admin.navigation.resources.team.fields.coach_phone_help'))
                            ->dehydrateStateUsing(fn ($state) => $state ? str_replace(' ', '', $state) : $state),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('admin.navigation.resources.team.actions.edit_coach_contact'))
                    ->visible(fn (): bool => auth()->user()->can('manage_rosters'))
                    ->form([
                        TextInput::make('email')
                            ->label(__('admin.navigation.resources.team.fields.coach_email'))
                            ->helperText(__('admin.navigation.resources.team.fields.coach_email_help'))
                            ->email(),
                        TextInput::make('phone')
                            ->label(__('admin.navigation.resources.team.fields.coach_phone'))
                            ->helperText(__('admin.navigation.resources.team.fields.coach_phone_help'))
                            ->dehydrateStateUsing(fn ($state) => $state ? str_replace(' ', '', $state) : $state),
                    ]),
                Action::make('edit_user')
                    ->label(__('user.actions.edit_user'))
                    ->icon(IconHelper::get(IconHelper::USER))
                    ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
                DetachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.detach'))
                    ->icon(IconHelper::get(IconHelper::TRASH))
                    ->visible(fn (): bool => auth()->user()->can('manage_rosters')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label(__('admin.navigation.resources.team.actions.detach_selected')),
                ])->visible(fn (): bool => auth()->user()->can('manage_rosters')),
            ]);
    }
}

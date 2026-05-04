<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\MembershipStatus;
use App\Filament\Resources\Users\UserResource;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label(__('user.actions.activate'))
                ->icon(IconHelper::get(IconHelper::ACTIVATE))
                ->color('success')
                ->visible(fn ($record) => ! $record->is_active || $record->membership_status !== MembershipStatus::Active)
                ->action(function ($record) {
                    $record->update([
                        'is_active' => true,
                        'membership_status' => MembershipStatus::Active,
                    ]);
                    Notification::make()
                        ->title(__('user.actions.activate'))
                        ->success()
                        ->send();
                }),
            Action::make('deactivate')
                ->label(__('user.actions.deactivate'))
                ->icon(IconHelper::get(IconHelper::DEACTIVATE))
                ->color('danger')
                ->visible(fn ($record) => $record->is_active || $record->membership_status === MembershipStatus::Active)
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'is_active' => false,
                        'membership_status' => MembershipStatus::Inactive,
                    ]);
                    Notification::make()
                        ->title(__('user.actions.deactivate'))
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}

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
        return $table
            ->striped()
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['externalMappings', 'roles', 'playerProfile.primaryTeam', 'media'])
            )
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
                TextColumn::make('roles.display_name')
                    ->label(__('user.fields.roles'))
                    ->badge()
                    ->color('info')
                    ->separator(','),
                TextColumn::make('membership_status')
                    ->label(__('user.fields.membership_status'))
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('user.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->two_factor_confirmed_at !== null)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->headerActions([
                //
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

        return $table;
    }
}

<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlayersRelationManager extends RelationManager
{
    protected static string $relationship = 'players';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.resources.team.fields.players');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user.name')
                    ->label(__('user.fields.full_name'))
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('role_in_team')
                    ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                    ->maxLength(255),
                Checkbox::make('is_primary_team')
                    ->label(__('admin.navigation.resources.team.fields.is_primary_team')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('user.fields.full_name'))
                    ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jersey_number')
                    ->label(__('admin.navigation.resources.player_profile.fields.jersey_number'))
                    ->sortable(),
                TextColumn::make('pivot.role_in_team')
                    ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                    ->searchable(),
                IconColumn::make('pivot.is_primary_team')
                    ->label(__('admin.navigation.resources.team.fields.is_primary_team'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.attach_player'))
                    ->icon(IconHelper::get(IconHelper::PLUS))
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('role_in_team')
                            ->label(__('admin.navigation.resources.team.fields.role_in_team')),
                        Checkbox::make('is_primary_team')
                            ->label(__('admin.navigation.resources.team.fields.is_primary_team')),
                    ]),
            ])
            ->recordActions([
                Action::make('edit_user')
                    ->label(__('user.actions.edit_user'))
                    ->icon(IconHelper::get(IconHelper::USER))
                    ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                    ->icon(IconHelper::get(IconHelper::EDIT)),
                DetachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.detach'))
                    ->icon(IconHelper::get(IconHelper::TRASH)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label(__('admin.navigation.resources.team.actions.detach_selected')),
                ]),
            ]);
    }
}

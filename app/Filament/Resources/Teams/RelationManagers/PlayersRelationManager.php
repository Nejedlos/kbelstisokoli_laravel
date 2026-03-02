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

    protected function modifyQueryUsing(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $playerTable = $query->getModel()->getTable();
        $userTable = (new \App\Models\User())->getTable();

        return $query->with(['user'])
            ->where("{$playerTable}.is_active", true)
            ->whereHas('user', fn ($q) => $q->where("{$userTable}.is_active", true));
    }

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
                    ->default('player')
                    ->required()
                    ->maxLength(255),
                Checkbox::make('is_primary_team')
                    ->label(__('admin.navigation.resources.team.fields.is_primary_team'))
                    ->default(false),
                Checkbox::make('is_on_roster')
                    ->label(__('Hráč je na soupisce'))
                    ->default(false),
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
                IconColumn::make('pivot.is_on_roster')
                    ->label(__('Soupiska'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.attach_player'))
                    ->icon(IconHelper::render(IconHelper::PLUS))
                    ->visible(fn (): bool => auth()->user()->can('manage_rosters'))
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (\Illuminate\Database\Eloquent\Builder $query) {
                        $userTable = (new \App\Models\User())->getTable();
                        $playerTable = $query->getModel()->getTable();

                        return $query
                            ->join($userTable, "{$playerTable}.user_id", '=', "{$userTable}.id")
                            ->select("{$playerTable}.*", "{$userTable}.name as user_name_title")
                            ->orderBy("{$userTable}.name");
                    })
                    ->recordTitleAttribute('user_name_title')
                    ->recordSelectSearchColumns([(new \App\Models\User())->getTable().'.name'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('role_in_team')
                            ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                            ->default('player')
                            ->required(),
                        Checkbox::make('is_primary_team')
                            ->label(__('admin.navigation.resources.team.fields.is_primary_team'))
                            ->default(false),
                        Checkbox::make('is_on_roster')
                            ->label(__('Hráč je na soupisce'))
                            ->default(false),
                    ]),
            ])
            ->recordActions([
                Action::make('edit_user')
                    ->label(__('user.actions.edit_user'))
                    ->icon(IconHelper::render(IconHelper::USER))
                    ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record->user_id]))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                    ->icon(IconHelper::render(IconHelper::EDIT))
                    ->visible(fn (): bool => auth()->user()->can('manage_rosters'))
                    ->form([
                        TextInput::make('role_in_team')
                            ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                            ->required(),
                        Checkbox::make('is_primary_team')
                            ->label(__('admin.navigation.resources.team.fields.is_primary_team')),
                        Checkbox::make('is_on_roster')
                            ->label(__('Hráč je na soupisce')),
                    ]),
                DetachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.detach'))
                    ->icon(IconHelper::render(IconHelper::TRASH))
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

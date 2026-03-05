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
use Filament\Forms\Components\Select;
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
        $userTable = (new \App\Models\User)->getTable();

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
                Select::make('role_in_team')
                    ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                    ->options(__('admin.navigation.resources.team.fields.roles'))
                    ->default('player')
                    ->required()
                    ->native(false),
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
                    ->formatStateUsing(fn (string $state): string => __("admin.navigation.resources.team.fields.roles.{$state}") ?? $state)
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
                        $userTable = (new \App\Models\User)->getTable();
                        $playerTable = $query->getModel()->getTable();

                        return $query
                            ->join($userTable, "{$playerTable}.user_id", '=', "{$userTable}.id")
                            ->select("{$playerTable}.*", "{$userTable}.name as user_name_title")
                            ->orderBy("{$userTable}.name");
                    })
                    ->recordTitleAttribute('user.name')
                    ->recordSelectSearchColumns(['user.name'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role_in_team')
                            ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                            ->options(__('admin.navigation.resources.team.fields.roles'))
                            ->default('player')
                            ->required()
                            ->native(false),
                        Checkbox::make('is_primary_team')
                            ->label(__('admin.navigation.resources.team.fields.is_primary_team'))
                            ->default(true),
                        Checkbox::make('is_on_roster')
                            ->label(__('Hráč je na soupisce'))
                            ->default(false),
                    ])
                    ->after(function (\App\Models\PlayerProfile $record, array $data, RelationManager $livewire) {
                        if ($data['is_primary_team'] ?? false) {
                            $currentTeamId = $livewire->getOwnerRecord()->id;

                            // Zrušíme příznak primárního týmu u ostatních týmů v pivotu (správně cíleně podle ID)
                            $allTeamIds = $record->teams()->pluck('teams.id')->all();
                            $otherTeamIds = array_values(array_filter($allTeamIds, fn ($id) => (int) $id !== (int) $currentTeamId));
                            if (! empty($otherTeamIds)) {
                                $record->teams()->updateExistingPivot($otherTeamIds, ['is_primary_team' => false]);
                            }

                            // Ujistíme se, že aktuální tým má v pivotu nastavený příznak primárního týmu
                            $record->teams()->updateExistingPivot($currentTeamId, ['is_primary_team' => true]);

                            // Aktualizujeme primary_team_id v profilu
                            $record->update(['primary_team_id' => $currentTeamId]);
                        }
                    }),
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
                        Select::make('role_in_team')
                            ->label(__('admin.navigation.resources.team.fields.role_in_team'))
                            ->options(__('admin.navigation.resources.team.fields.roles'))
                            ->default('player')
                            ->required()
                            ->native(false),
                        Checkbox::make('is_primary_team')
                            ->label(__('admin.navigation.resources.team.fields.is_primary_team')),
                        Checkbox::make('is_on_roster')
                            ->label(__('Hráč je na soupisce')),
                    ])
                    ->after(function (\App\Models\PlayerProfile $record, array $data, RelationManager $livewire) {
                        $currentTeamId = $livewire->getOwnerRecord()->id;

                        if ($data['is_primary_team'] ?? false) {
                            // Zrušíme příznak u ostatních týmů (správně cíleně podle ID)
                            $allTeamIds = $record->teams()->pluck('teams.id')->all();
                            $otherTeamIds = array_values(array_filter($allTeamIds, fn ($id) => (int) $id !== (int) $currentTeamId));
                            if (! empty($otherTeamIds)) {
                                $record->teams()->updateExistingPivot($otherTeamIds, ['is_primary_team' => false]);
                            }

                            // Ujistíme se, že aktuální tým má v pivotu nastavený příznak primárního týmu
                            $record->teams()->updateExistingPivot($currentTeamId, ['is_primary_team' => true]);

                            // Aktualizujeme profil
                            $record->update(['primary_team_id' => $currentTeamId]);
                        } elseif ($record->primary_team_id === $currentTeamId) {
                            // Pokud to byl primární tým a uživatel jej odškrtl, nastavíme primary_team_id na null
                            $record->update(['primary_team_id' => null]);

                            // A v pivotu zrušíme příznak pro tento tým
                            $record->teams()->updateExistingPivot($currentTeamId, ['is_primary_team' => false]);
                        }
                    }),
                DetachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.detach'))
                    ->icon(IconHelper::render(IconHelper::TRASH))
                    ->visible(fn (): bool => auth()->user()->can('manage_rosters'))
                    ->after(function (\App\Models\PlayerProfile $record, RelationManager $livewire) {
                        if ($record->primary_team_id === $livewire->getOwnerRecord()->id) {
                            $record->update(['primary_team_id' => null]);
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label(__('admin.navigation.resources.team.actions.detach_selected'))
                        ->after(function (\Illuminate\Support\Collection $records, RelationManager $livewire) {
                            $teamId = $livewire->getOwnerRecord()->id;
                            foreach ($records as $record) {
                                if ($record->primary_team_id === $teamId) {
                                    $record->update(['primary_team_id' => null]);
                                }
                            }
                        }),
                ])->visible(fn (): bool => auth()->user()->can('manage_rosters')),
            ]);
    }
}

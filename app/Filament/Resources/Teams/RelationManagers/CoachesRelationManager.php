<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('user.fields.phone'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label(__('admin.navigation.resources.team.actions.attach_coach'))
                    ->icon(IconHelper::get(IconHelper::PLUS))
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('email')
                            ->label(__('admin.navigation.resources.team.fields.coach_email'))
                            ->email(),
                    ]),
            ])
            ->recordActions([
                Action::make('edit_user')
                    ->label(__('user.actions.edit_user'))
                    ->icon(IconHelper::get(IconHelper::USER))
                    ->url(fn ($record): string => UserResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
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

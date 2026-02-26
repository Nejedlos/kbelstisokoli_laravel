<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTeam extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_public')
                ->label(__('admin.navigation.resources.team.actions.view_public'))
                ->icon(IconHelper::get(IconHelper::GLOBE))
                ->url(fn ($record) => route('public.teams.show', $record->slug))
                ->openUrlInNewTab()
                ->color('gray'),
            DeleteAction::make(),
        ];
    }
}

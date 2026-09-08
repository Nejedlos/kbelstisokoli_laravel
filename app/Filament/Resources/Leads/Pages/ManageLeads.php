<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Resources\Leads\LeadResource;
use App\Models\LeadRoutingSetting;
use App\Services\LeadWorkflowService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ManageRecords;

class ManageLeads extends ManageRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('defaultResponsible')
                ->label(__('leads.fields.responsible'))
                ->form([
                    Select::make('default_responsible_user_id')
                        ->label(__('leads.fields.responsible'))
                        ->options(fn () => app(LeadWorkflowService::class)->eligibleUsers()->pluck('name', 'id'))
                        ->searchable()
                        ->helperText(__('leads.not_provided').': '.config('leads.technical_contact_email')),
                ])
                ->fillForm(fn (): array => [
                    'default_responsible_user_id' => LeadRoutingSetting::query()->value('default_responsible_user_id'),
                ])
                ->action(function (array $data): void {
                    LeadRoutingSetting::query()->updateOrCreate([], $data);
                }),
        ];
    }
}

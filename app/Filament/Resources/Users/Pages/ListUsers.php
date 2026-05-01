<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function mount(): void
    {
        \Illuminate\Support\Facades\Log::debug('ListUsers: mount start');
        parent::mount();
        \Illuminate\Support\Facades\Log::debug('ListUsers: mount end');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

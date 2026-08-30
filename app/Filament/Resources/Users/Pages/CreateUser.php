<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Services\Users\MembershipRoleSynchronizer;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function afterCreate(): void
    {
        app(MembershipRoleSynchronizer::class)->sync($this->record);
    }
}

<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }
}

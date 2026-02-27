<?php

namespace App\Filament\Resources\UserSeasonConfigs\Pages;

use App\Filament\Resources\UserSeasonConfigs\UserSeasonConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserSeasonConfig extends CreateRecord
{
    protected static string $resource = UserSeasonConfigResource::class;
}

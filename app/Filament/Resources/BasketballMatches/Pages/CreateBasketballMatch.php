<?php

namespace App\Filament\Resources\BasketballMatches\Pages;

use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use App\Filament\Resources\BasketballMatches\Pages\Concerns\PersistsMatchSafely;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBasketballMatch extends CreateRecord
{
    use PersistsMatchSafely;

    protected static string $resource = BasketballMatchResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return $this->persistMatchSafely($data);
    }
}

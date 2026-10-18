<?php

namespace App\Filament\Resources\BasketballMatches\Pages;

use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use App\Filament\Resources\BasketballMatches\Pages\Concerns\PersistsMatchSafely;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBasketballMatch extends EditRecord
{
    use PersistsMatchSafely;

    protected static string $resource = BasketballMatchResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->persistMatchSafely($data, $record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use App\Models\Training;
use App\Support\TrainingRecurringHelper;
use Filament\Resources\Pages\CreateRecord;

class CreateTraining extends CreateRecord
{
    protected static string $resource = TrainingResource::class;

    protected function afterCreate(): void
    {
        /** @var Training $record */
        $record = $this->record;
        $data = $this->data;

        TrainingRecurringHelper::replicate($record, $data);
    }
}

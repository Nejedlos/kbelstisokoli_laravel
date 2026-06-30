<?php

namespace App\Filament\Resources\Trainings\Pages;

use App\Filament\Resources\Trainings\TrainingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateTraining extends CreateRecord
{
    protected static string $resource = TrainingResource::class;

    protected function afterCreate(): void
    {
        /** @var \App\Models\Training $record */
        $record = $this->record;
        $data = $this->data;

        if (empty($data['repeat_frequency'])) {
            return;
        }

        $startsAt = Carbon::parse($record->starts_at);
        $endsAt = $record->ends_at ? Carbon::parse($record->ends_at) : null;
        $diffInMinutes = $endsAt ? $startsAt->diffInMinutes($endsAt) : null;

        $dates = [];
        $frequency = $data['repeat_frequency'];
        $count = (int) ($data['repeat_count'] ?? 0);
        $period = $data['repeat_period'] ?? null;

        if ($count > 0) {
            for ($i = 1; $i <= $count; $i++) {
                $dates[] = $this->getNextDate($startsAt, $frequency, $i);
            }
        } elseif ($period) {
            $endDate = $this->getEndDateFromPeriod($startsAt, $period);
            $i = 1;
            while (true) {
                $nextDate = $this->getNextDate($startsAt, $frequency, $i);
                if ($nextDate->gt($endDate)) {
                    break;
                }
                $dates[] = $nextDate;
                $i++;
                if ($i > 100) {
                    break;
                } // Safety limit
            }
        }

        foreach ($dates as $date) {
            $newStartsAt = $date;
            $newEndsAt = $diffInMinutes !== null ? $date->copy()->addMinutes($diffInMinutes) : null;

            $newTraining = $record->replicate();
            $newTraining->starts_at = $newStartsAt;
            $newTraining->ends_at = $newEndsAt;
            $newTraining->save();

            // Sync teams (M:N relation)
            if ($record->teams()->exists()) {
                $newTraining->teams()->sync($record->teams->pluck('id')->toArray());
            }
        }
    }

    protected function getNextDate(Carbon $baseDate, string $frequency, int $step): Carbon
    {
        $date = $baseDate->copy();

        return match ($frequency) {
            'daily' => $date->addDays($step),
            'weekly' => $date->addWeeks($step),
            'monthly' => $date->addMonths($step),
            default => $date,
        };
    }

    protected function getEndDateFromPeriod(Carbon $baseDate, string $period): Carbon
    {
        return match ($period) {
            '1_month' => $baseDate->copy()->addMonth(),
            '2_months' => $baseDate->copy()->addMonths(2),
            '3_months' => $baseDate->copy()->addMonths(3),
            '6_months' => $baseDate->copy()->addMonths(6),
            'this_season' => $this->getSeasonEnd($baseDate),
            default => $baseDate->copy()->addMonth(),
        };
    }

    protected function getSeasonEnd(Carbon $date): Carbon
    {
        $year = $date->year;
        $month = $date->month;

        // Season ends July 31st
        if ($month >= 8) {
            return Carbon::create($year + 1, 7, 31)->endOfDay();
        }

        return Carbon::create($year, 7, 31)->endOfDay();
    }
}

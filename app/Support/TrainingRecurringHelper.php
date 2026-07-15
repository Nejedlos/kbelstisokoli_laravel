<?php

namespace App\Support;

use App\Models\Training;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TrainingRecurringHelper
{
    /**
     * Replikuje trénink podle zadaných parametrů.
     *
     * @param Training $training Původní trénink
     * @param array $data Data z formuláře (repeat_frequency, repeat_count, repeat_period, target_date atd.)
     * @return Collection<int, Training> Kolekce vytvořených tréninků
     */
    public static function replicate(Training $training, array $data): Collection
    {
        $createdTrainings = new Collection();

        $startsAt = Carbon::parse($training->starts_at);
        $endsAt = $training->ends_at ? Carbon::parse($training->ends_at) : null;
        $diffInMinutes = $endsAt ? $startsAt->diffInMinutes($endsAt) : null;

        $dates = [];

        // 1. Jednorázové klonování na konkrétní datum
        if (!empty($data['target_date'])) {
            $targetDate = Carbon::parse($data['target_date']);
            
            // Zachováme čas z původního tréninku, změníme jen datum
            $newDate = $targetDate->copy()->setTime(
                $startsAt->hour,
                $startsAt->minute,
                $startsAt->second
            );
            $dates[] = $newDate;
        } 
        // 2. Opakované klonování (původní logika z CreateTraining)
        elseif (!empty($data['repeat_frequency'])) {
            $frequency = $data['repeat_frequency'];
            $count = (int) ($data['repeat_count'] ?? 0);
            $period = $data['repeat_period'] ?? null;

            if ($count > 0) {
                for ($i = 1; $i <= $count; $i++) {
                    $dates[] = self::getNextDate($startsAt, $frequency, $i);
                }
            } elseif ($period) {
                $endDate = self::getEndDateFromPeriod($startsAt, $period);
                $i = 1;
                while (true) {
                    $nextDate = self::getNextDate($startsAt, $frequency, $i);
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
        }

        foreach ($dates as $date) {
            $newStartsAt = $date;
            $newEndsAt = $diffInMinutes !== null ? $date->copy()->addMinutes($diffInMinutes) : null;

            /** @var Training $newTraining */
            $newTraining = $training->replicate([
                'mismatches_count',
                'attendances_count',
                'confirmed_count',
                'declined_count',
            ]);
            $newTraining->starts_at = $newStartsAt;
            $newTraining->ends_at = $newEndsAt;
            $newTraining->save();

            // Sync týmů
            if ($training->teams()->exists()) {
                $newTraining->teams()->sync($training->teams->pluck('id')->toArray());
            }

            $createdTrainings->push($newTraining);
        }

        return $createdTrainings;
    }

    protected static function getNextDate(Carbon $baseDate, string $frequency, int $step): Carbon
    {
        $date = $baseDate->copy();

        return match ($frequency) {
            'daily' => $date->addDays($step),
            'weekly' => $date->addWeeks($step),
            'monthly' => $date->addMonths($step),
            default => $date,
        };
    }

    protected static function getEndDateFromPeriod(Carbon $baseDate, string $period): Carbon
    {
        return match ($period) {
            '1_month' => $baseDate->copy()->addMonth(),
            '2_months' => $baseDate->copy()->addMonths(2),
            '3_months' => $baseDate->copy()->addMonths(3),
            '6_months' => $baseDate->copy()->addMonths(6),
            'this_season' => self::getSeasonEnd($baseDate),
            default => $baseDate->copy()->addMonth(),
        };
    }

    protected static function getSeasonEnd(Carbon $date): Carbon
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

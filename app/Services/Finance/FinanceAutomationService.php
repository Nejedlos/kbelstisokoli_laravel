<?php

namespace App\Services\Finance;

use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\ClubEvent;
use App\Models\FinanceCharge;
use App\Models\FinancialTariff;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\Training;
use App\Models\User;
use App\Models\UserSeasonConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FinanceAutomationService
{
    /**
     * Finálně uzavře docházku a vygeneruje pokuty pro všechny, kteří se nevyjádřili
     * nebo nebyli přítomni a nemají omluvu.
     */
    public function finalizeAttendance($event): void
    {
        $season = Season::forDate($event->starts_at ?? $event->scheduled_at);
        if (!$season) {
            return;
        }

        $teams = $event->teams;
        $userIds = $teams->flatMap(fn($team) => $team->activePlayers->pluck('user_id'))->unique();

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $attendance = Attendance::where('user_id', $userId)
                ->where('attendable_type', get_class($event))
                ->where('attendable_id', $event->id)
                ->first();

            // Pokud záznam neexistuje, vytvoříme ho jako pending/absent
            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id' => $userId,
                    'attendable_type' => get_class($event),
                    'attendable_id' => $event->id,
                    'planned_status' => 'pending',
                    'actual_status' => 'absent',
                ]);
            } else {
                // Pokud záznam existuje ale actual_status je null, nastavíme absent
                if ($attendance->actual_status === null) {
                    $attendance->update(['actual_status' => 'absent']);
                }
            }
        }

        // Nyní, když mají všichni nastavený actual_status, spočítáme pokuty
        $this->processAttendanceFines($event);

        // Uložíme informaci o uzavření do metadat události
        $metadata = $event->metadata ?? [];
        $metadata['attendance_finalized_at'] = now()->toDateTimeString();
        $event->update(['metadata' => $metadata]);
    }

    /**
     * Zpracuje pokuty za docházku u konkrétní události.
     */
    public function processAttendanceFines($event): void
    {
        $season = Season::forDate($event->starts_at ?? $event->scheduled_at);
        if (!$season) {
            return;
        }

        $eventType = class_basename($event);
        $eventId = $event->id;

        // Načteme všechny členy z týmů přiřazených k akci
        $teams = $event->teams;
        $userIds = $teams->flatMap(fn($team) => $team->activePlayers->pluck('user_id'))->unique();

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $config = UserSeasonConfig::where('user_id', $userId)
                ->where('season_id', $season->id)
                ->first();

            if (!$config || !$config->tariff) continue;

            $tariff = $config->tariff;

            // 1. Zpracování čerpání (pay-per-event nebo prepaid)
            if ($tariff->type === 'per_event' || $tariff->type === 'prepaid') {
                $attendance = Attendance::where('user_id', $userId)
                    ->where('attendable_type', get_class($event))
                    ->where('attendable_id', $eventId)
                    ->first();

                if ($attendance && $attendance->actual_status === 'attended') {
                    if ($tariff->type === 'per_event') {
                        $this->createPayPerEventCharge($user, $event, $tariff);
                    } else {
                        $this->processPrepaidEventUsage($user, $event, $config, $tariff);
                    }
                }
            }

            // 2. Zpracování pokut (pokud je aktivní v tarifu)
            if ($tariff->calculate_attendance_fines) {
                $this->processSingleAttendanceFine($user, $event, $season, $tariff);
            }
        }
    }

    protected function processPrepaidEventUsage(User $user, $event, UserSeasonConfig $config, FinancialTariff $tariff): void
    {
        $usedCount = (int) ($config->metadata['used_prepaid_events'] ?? 0);
        $limit = (int) ($tariff->prepaid_events_count ?? 0);

        // Unikátní klíč pro tuto událost, abychom nezapočítali čerpání dvakrát
        $uniqueKey = "usage:prepaid:".Str::snake(class_basename($event)).":{$event->id}:user:{$user->id}";

        // Zkontrolujeme v metadatech configu, zda už tato událost nebyla započtena
        $recordedUsages = $config->metadata['prepaid_usage_keys'] ?? [];
        if (in_array($uniqueKey, $recordedUsages)) {
            return;
        }

        if ($usedCount < $limit) {
            // Čerpáme z balíčku
            $usedCount++;
            $recordedUsages[] = $uniqueKey;

            $metadata = $config->metadata ?? [];
            $metadata['used_prepaid_events'] = $usedCount;
            $metadata['prepaid_usage_keys'] = $recordedUsages;

            $config->update(['metadata' => $metadata]);

            // Můžeme volitelně přidat info do Attendance, že bylo čerpáno z balíčku
            $attendance = Attendance::where('user_id', $user->id)
                ->where('attendable_type', get_class($event))
                ->where('attendable_id', $event->id)
                ->first();

            if ($attendance) {
                $attMetadata = $attendance->metadata ?? [];
                $attMetadata['prepaid_usage'] = [
                    'count_at_time' => $usedCount,
                    'limit' => $limit,
                    'type' => 'package',
                ];
                $attendance->update(['metadata' => $attMetadata]);
            }
        } else {
            // Vyčerpáno, účtujeme extra charge
            $eventDate = Carbon::parse($event->starts_at ?? $event->scheduled_at)->format('d.m.Y');
            $chargeUniqueKey = "charge:extra:".Str::snake(class_basename($event)).":{$event->id}:user:{$user->id}";

            $this->createChargeIfNotExists([
                'user_id' => $user->id,
                'title' => "Poplatek nad rámec balíčku: {$eventDate}",
                'amount_total' => $tariff->extra_event_amount,
                'charge_type' => 'event_fee',
                'due_date' => now()->addDays(14),
                'metadata' => [
                    'incident_key' => $chargeUniqueKey,
                    'event_type' => get_class($event),
                    'event_id' => $event->id,
                    'prepaid_exhausted' => true,
                ]
            ]);
        }
    }

    /**
     * Zpracuje pokuty za trestné hody z řádku statistik.
     */
    public function processThFines(StatisticRow $row): void
    {
        $user = $row->user;
        if (!$user) return;

        $match = $row->match;
        if (!$match) return;

        $season = $match->season;
        if (!$season || $season->fine_missed_free_throw <= 0) return;

        $config = UserSeasonConfig::where('user_id', $user->id)
            ->where('season_id', $season->id)
            ->first();

        if (!$config || !$config->tariff || !$config->tariff->calculate_th_fines) return;

        $fta = (int) ($row->stats['FTA'] ?? 0);
        $ftm = (int) ($row->stats['FTM'] ?? 0);
        $missed = $fta - $ftm;

        if ($missed <= 0) return;

        $amount = $missed * $season->fine_missed_free_throw;
        $uniqueKey = "fine:th:match:{$match->id}:user:{$user->id}";

        $this->createChargeIfNotExists([
            'user_id' => $user->id,
            'title' => "Pokuta: Neproměněné TH ({$missed}x) - Zápas proti {$match->getOfficialOpponentNameAttribute()}",
            'amount_total' => $amount,
            'charge_type' => 'fine',
            'due_date' => now()->addDays(14),
            'metadata' => [
                'incident_key' => $uniqueKey,
                'match_id' => $match->id,
                'missed_count' => $missed,
            ]
        ]);
    }

    /**
     * Vygeneruje splátky pro daný uživatelský tarif.
     */
    public function generateInstallments(UserSeasonConfig $config): void
    {
        $tariff = $config->tariff;
        if (!$tariff) {
            return;
        }

        if ($tariff->type === 'flat' && !empty($tariff->installment_plan)) {
            foreach ($tariff->installment_plan as $installment) {
                $label = $installment['label'] ?? 'Splátka';
                $amount = $installment['amount'] ?? 0;
                $dueDate = isset($installment['due_date']) ? Carbon::parse($installment['due_date']) : now()->addMonth();
                $slug = Str::slug($label);

                $uniqueKey = "charge:installment:tariff:{$tariff->id}:user:{$config->user_id}:slug:{$slug}";

                $this->createChargeIfNotExists([
                    'user_id' => $config->user_id,
                    'title' => "{$tariff->name} - {$label}",
                    'amount_total' => $amount,
                    'charge_type' => 'tariff',
                    'due_date' => $dueDate,
                    'metadata' => [
                        'incident_key' => $uniqueKey,
                        'tariff_id' => $tariff->id,
                        'season_id' => $config->season_id,
                    ]
                ]);
            }
        } elseif ($tariff->type === 'prepaid') {
            $uniqueKey = "charge:prepaid:tariff:{$tariff->id}:user:{$config->user_id}";

            // Resetujeme countery pro čerpání při (znovu)generování splátek pro prepaid
            $metadata = $config->metadata ?? [];
            $metadata['used_prepaid_events'] = 0;
            $metadata['prepaid_usage_keys'] = [];
            $config->update(['metadata' => $metadata]);

            $this->createChargeIfNotExists([
                'user_id' => $config->user_id,
                'title' => "{$tariff->name} - Předplacený balíček ({$tariff->prepaid_events_count} akcí)",
                'amount_total' => $tariff->base_amount,
                'charge_type' => 'tariff',
                'due_date' => now()->addMonth(),
                'metadata' => [
                    'incident_key' => $uniqueKey,
                    'tariff_id' => $tariff->id,
                    'season_id' => $config->season_id,
                    'is_prepaid' => true,
                    'prepaid_events_count' => $tariff->prepaid_events_count,
                ]
            ]);
        }
    }

    protected function processSingleAttendanceFine(User $user, $event, Season $season, FinancialTariff $tariff): void
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->where('attendable_type', get_class($event))
            ->where('attendable_id', $event->id)
            ->first();

        $eventTypeLabel = match (get_class($event)) {
            Training::class => 'tréninku',
            BasketballMatch::class => 'zápasu',
            default => 'akci',
        };

        $eventDate = Carbon::parse($event->starts_at ?? $event->scheduled_at)->format('d.m.Y');
        $baseTitle = "Automatická pokuta: ";
        $amount = 0;
        $reason = "";

        // Stavy:
        // 1. Nezadání (pendning -> absent) - fine_no_response
        // 2. Potvrzeno a nepřišel (confirmed -> absent) - fine_no_show
        // 3. Nezadání a přišel (pending -> attended) - fine_unannounced_show
        // 4. Omluveno a přišel (declined -> attended) - fine_excused_show

        if (!$attendance) {
            // Hráč vůbec nereagoval a ani tam nebyl? (Předpokládáme absent pokud není v Attendance)
            // Ale trenér mohl zadat docházku.
            // Pokud není záznam v Attendance, bereme to jako pending.
            return; // Pokud není ani záznam, zatím neřešíme, počkáme až trenér někoho označí.
        }

        if ($attendance->actual_status !== 'attended') {
            // Nepřišel
            if ($attendance->planned_status === 'pending') {
                $amount = $season->fine_no_response;
                $reason = "Nevyjádření se k {$eventTypeLabel} ({$eventDate})";
            } elseif ($attendance->planned_status === 'confirmed') {
                $amount = $season->fine_no_show;
                $reason = "Neomluvená absence na {$eventTypeLabel} ({$eventDate})";
            }
        } else {
            // Přišel
            if ($attendance->planned_status === 'pending') {
                $amount = $season->fine_unannounced_show;
                $reason = "Nenahlášená účast na {$eventTypeLabel} ({$eventDate})";
            } elseif ($attendance->planned_status === 'declined') {
                $amount = $season->fine_excused_show;
                $reason = "Účast i přes omluvu na {$eventTypeLabel} ({$eventDate})";
            }
        }

        if ($amount > 0) {
            $uniqueKey = "fine:attendance:".Str::snake(class_basename($event)).":{$event->id}:user:{$user->id}";

            $this->createChargeIfNotExists([
                'user_id' => $user->id,
                'title' => $baseTitle . $reason,
                'amount_total' => $amount,
                'charge_type' => 'fine',
                'due_date' => now()->addDays(14),
                'metadata' => [
                    'incident_key' => $uniqueKey,
                    'event_type' => get_class($event),
                    'event_id' => $event->id,
                ]
            ]);
        }
    }

    protected function createPayPerEventCharge(User $user, $event, FinancialTariff $tariff): void
    {
        $eventDate = Carbon::parse($event->starts_at ?? $event->scheduled_at)->format('d.m.Y');
        $uniqueKey = "charge:event:".Str::snake(class_basename($event)).":{$event->id}:user:{$user->id}";

        $this->createChargeIfNotExists([
            'user_id' => $user->id,
            'title' => "Poplatek za účast: {$eventDate}",
            'amount_total' => $tariff->base_amount,
            'charge_type' => 'event_fee',
            'due_date' => now()->addDays(14),
            'metadata' => [
                'incident_key' => $uniqueKey,
                'event_type' => get_class($event),
                'event_id' => $event->id,
            ]
        ]);
    }

    protected function createChargeIfNotExists(array $data): ?FinanceCharge
    {
        $incidentKey = $data['metadata']['incident_key'] ?? null;

        if ($incidentKey) {
            // Hledáme v metadata (JSON v DB) bez použití JSON operátoru pro kompatibilitu
            $exists = FinanceCharge::where('metadata', 'LIKE', '%"incident_key":"' . $incidentKey . '"%')->exists();
            if ($exists) {
                return null;
            }
        }

        return FinanceCharge::create(array_merge($data, [
            'status' => 'open',
            'currency' => 'CZK',
            'is_visible_to_member' => true,
        ]));
    }
}

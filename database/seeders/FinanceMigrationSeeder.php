<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FinanceMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oldDb = config('database.old_database');
        if (! $oldDb) {
            $this->command->error('Databáze pro migraci nebyla nalezena (DB_DATABASE_OLD ani DB_DATABASE).');

            return;
        }

        $this->command->info('Migruji finanční data ze staré DB...');

        \Illuminate\Support\Facades\DB::disableQueryLog();

        try {
            // 1. Mapování uživatelů
            $usersByLegacyId = \App\Models\User::all()->mapWithKeys(function ($user) {
                $legacyId = $user->metadata['legacy_r_id'] ?? null;

                return $legacyId ? [$legacyId => $user] : [];
            });

            // 2. Načtení sezón pro mapování
            $seasonsByName = \App\Models\Season::all()->keyBy('name');

            // 3. Migrace Finančních tarifů
            $this->migrateTariffs($oldDb);
            $tariffsByLegacyId = \App\Models\FinancialTariff::all()->mapWithKeys(function ($t) {
                $legacyId = $t->metadata['legacy_id'] ?? 0;

                return [$legacyId => $t];
            });

            // 3.5 Migrace Šablon pokut
            $this->call(FineTemplateSeeder::class);
            $fineTemplatesByLegacyId = \App\Models\FineTemplate::all()->mapWithKeys(function ($t) {
                $legacyId = $t->metadata['legacy_id'] ?? 0;

                return [$legacyId => $t];
            });

            // 4. Migrace Sezónních konfigurací (web_platici)
            $this->migrateUserSeasonConfigs($oldDb, $usersByLegacyId, $seasonsByName, $tariffsByLegacyId);

            // 5. Migrace "Plátců" (roční paušály) - nyní z našich nových konfigurací
            $this->migratePayerChargesFromConfigs();

            // 6. Migrace pokut
            $this->migrateFines($oldDb, $usersByLegacyId, $fineTemplatesByLegacyId);

            // 7. Migrace plateb
            $this->migratePayments($oldDb, $usersByLegacyId);

            // 8. Automatické párování (alokace)
            $this->autoAllocatePayments();

            $this->command->info('Migrace financí dokončena.');

        } catch (\Exception $e) {
            $this->command->error('Chyba při migraci financí: '.$e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }

    protected function migrateTariffs($oldDb)
    {
        $this->command->info('Migruji finanční tarify...');
        $oldTariffs = \Illuminate\Support\Facades\DB::connection('old_mysql')->table($oldDb.'.web_vypocty_platby')->get();

        $existingAll = \App\Models\FinancialTariff::all();

        foreach ($oldTariffs as $ot) {
            $existing = $existingAll->first(fn ($t) => ($t->metadata['legacy_id'] ?? null) == $ot->id);

            $tariffData = [
                'name' => $ot->nazev,
                'base_amount' => $ot->pausal,
                'unit' => str_contains(strtolower($ot->jednotka), 'měsíc') ? 'month' : 'season',
                'description' => $ot->vypocet,
                'metadata' => ['legacy_id' => $ot->id],
            ];

            if ($existing) {
                $existing->update($tariffData);
            } else {
                \App\Models\FinancialTariff::create($tariffData);
            }
        }
    }

    protected function migrateUserSeasonConfigs($oldDb, $usersByLegacyId, $seasonsByName, $tariffsByLegacyId)
    {
        $this->command->info('Migruji sezónní konfigurace uživatelů...');
        $payers = \Illuminate\Support\Facades\DB::connection('old_mysql')->table($oldDb.'.web_platici')->get();

        foreach ($payers as $payer) {
            $user = $usersByLegacyId->get($payer->r_id);
            if (! $user) {
                continue;
            }

            $seasonName = str_replace('-', '/', $payer->sezona);
            $season = $seasonsByName->get($seasonName);
            if (! $season) {
                // Pokud sezóna neexistuje, vytvoříme ji
                $season = \App\Models\Season::create(['name' => $seasonName, 'is_active' => false]);
                $seasonsByName->put($season->name, $season);
            }

            $tariff = $tariffsByLegacyId->get($payer->druh);
            if (! $tariff) {
                continue;
            }

            $existing = \App\Models\UserSeasonConfig::where(['user_id' => $user->id, 'season_id' => $season->id])->first();

            $configData = [
                'financial_tariff_id' => $tariff->id,
                'billing_start_month' => $payer->uctovat_od,
                'billing_end_month' => $payer->uctovat_do,
                'exemption_start_month' => $payer->osvobozen_od,
                'exemption_end_month' => $payer->osvobozen_do,
                'track_attendance' => $payer->hlidat_dochazku === 'ano',
                'opening_balance' => $payer->prevod_penez,
                'metadata' => ['legacy_id' => $payer->id],
            ];

            if ($existing) {
                $existing->update($configData);
            } else {
                \App\Models\UserSeasonConfig::create(array_merge(['user_id' => $user->id, 'season_id' => $season->id], $configData));
            }
        }
    }

    protected function migratePayerChargesFromConfigs()
    {
        $this->command->info('Vytvářím předpisy z konfigurací...');
        $configs = \App\Models\UserSeasonConfig::with(['tariff', 'season'])->get();

        $existingCharges = \App\Models\FinanceCharge::where('charge_type', 'membership_fee')
            ->get()
            ->keyBy(fn ($c) => $c->metadata['legacy_p_id'] ?? null)
            ->forget(null);

        foreach ($configs as $config) {
            if (! $config->tariff || $config->tariff->base_amount <= 0) {
                continue;
            }

            // Parsování roku pro datum splatnosti (podpora / i -)
            $seasonYear = explode('/', str_replace('-', '/', $config->season->name))[0] ?? date('Y');
            $dueDate = \Carbon\Carbon::parse($seasonYear.'-01-01');

            $existing = $existingCharges->get($config->metadata['legacy_id'] ?? null);

            $chargeData = [
                'user_id' => $config->user_id,
                'title' => 'Členské příspěvky '.$config->season->name,
                'description' => $config->tariff->name.': '.$config->tariff->description,
                'amount_total' => $config->tariff->base_amount,
                'currency' => 'CZK',
                'due_date' => $dueDate,
                'status' => 'open',
                'metadata' => [
                    'legacy_p_id' => $config->metadata['legacy_id'] ?? null,
                    'season_config_id' => $config->id,
                ],
            ];

            if ($existing) {
                $existing->update($chargeData);
            } else {
                \App\Models\FinanceCharge::create(array_merge(['charge_type' => 'membership_fee'], $chargeData));
            }
        }
    }

    protected function migrateFines($oldDb, $usersByLegacyId, $fineTemplatesByLegacyId = null)
    {
        $this->command->info('Vytvářím předpisy z pokut...');
        $fines = \Illuminate\Support\Facades\DB::connection('old_mysql')->table($oldDb.'.web_pokuty')->get();

        // Potřebujeme propojit p_id zpět na uživatele přes web_platici
        $payers = \Illuminate\Support\Facades\DB::connection('old_mysql')->table($oldDb.'.web_platici')->get()->keyBy('id');

        $existingFines = \App\Models\FinanceCharge::where('charge_type', 'fine')
            ->get()
            ->keyBy(fn ($c) => $c->metadata['legacy_fine_id'] ?? null)
            ->forget(null);

        foreach ($fines as $fine) {
            $payer = $payers->get($fine->p_id);
            if (! $payer) {
                continue;
            }

            $user = $usersByLegacyId->get($payer->r_id);
            if (! $user) {
                continue;
            }

            $amount = $fine->castka * ($fine->pocet ?: 1);
            if ($amount <= 0) {
                continue;
            }

            $paidAt = $fine->kdy_zap > 0 ? \Carbon\Carbon::createFromTimestamp($fine->kdy_zap) : null;

            $existing = $existingFines->get($fine->id);

            $fineTemplate = $fineTemplatesByLegacyId ? $fineTemplatesByLegacyId->get($fine->druh) : null;

            $fineData = [
                'user_id' => $user->id,
                'title' => 'Pokuta: '.$fine->typ,
                'description' => $fineTemplate ? "Šablona: {$fineTemplate->name}. Původní ID: {$fine->id}" : "Původní ID: {$fine->id}",
                'charge_type' => 'fine',
                'amount_total' => $amount,
                'currency' => 'CZK',
                'due_date' => \Carbon\Carbon::createFromTimestamp($fine->kdy),
                'status' => $paidAt ? 'paid' : 'open',
                'metadata' => [
                    'legacy_fine_id' => $fine->id,
                    'legacy_p_id' => $fine->p_id,
                    'fine_template_id' => $fineTemplate?->id,
                    'paid_at_legacy' => $paidAt?->toDateTimeString(),
                ],
            ];

            if ($existing) {
                $existing->update($fineData);
            } else {
                \App\Models\FinanceCharge::create($fineData);
            }
        }
    }

    protected function migratePayments($oldDb, $usersByLegacyId)
    {
        $this->command->info('Migruji skutečné platby...');
        $payments = \Illuminate\Support\Facades\DB::connection('old_mysql')->table($oldDb.'.web_platby')->get();
        $payers = \Illuminate\Support\Facades\DB::connection('old_mysql')->table($oldDb.'.web_platici')->get()->keyBy('id');

        $existingPayments = \App\Models\FinancePayment::all()->keyBy(fn ($p) => $p->metadata['legacy_pay_id'] ?? null)->forget(null);

        foreach ($payments as $payment) {
            $payer = $payers->get($payment->p_id);
            if (! $payer) {
                continue;
            }

            $user = $usersByLegacyId->get($payer->r_id);
            if (! $user) {
                continue;
            }

            $existing = $existingPayments->get($payment->id);

            $paymentData = [
                'user_id' => $user->id,
                'amount' => $payment->kolik,
                'currency' => 'CZK',
                'paid_at' => \Carbon\Carbon::createFromTimestamp($payment->kdy),
                'payment_method' => $payment->typ === 'banka' ? 'bank_transfer' : 'cash',
                'source_note' => 'Migrováno ze starého systému',
                'status' => 'recorded',
                'metadata' => [
                    'legacy_pay_id' => $payment->id,
                    'legacy_p_id' => $payment->p_id,
                ],
            ];

            if ($existing) {
                $existing->update($paymentData);
            } else {
                \App\Models\FinancePayment::create($paymentData);
            }
        }
    }

    protected function autoAllocatePayments()
    {
        $this->command->info('Provádím automatické párování plateb k předpisům...');

        $users = \App\Models\User::has('financePayments')->get();

        foreach ($users as $user) {
            $payments = $user->financePayments()->orderBy('paid_at', 'asc')->get();
            $charges = $user->financeCharges()
                ->whereIn('status', ['open', 'partially_paid'])
                ->orderBy('due_date', 'asc')
                ->get();

            foreach ($payments as $payment) {
                $available = $payment->amount_available;
                if ($available <= 0) {
                    continue;
                }

                foreach ($charges as $charge) {
                    $remaining = $charge->amount_remaining;
                    if ($remaining <= 0) {
                        continue;
                    }

                    $toAllocate = min($available, $remaining);

                    \App\Models\ChargePaymentAllocation::create([
                        'finance_charge_id' => $charge->id,
                        'finance_payment_id' => $payment->id,
                        'amount' => $toAllocate,
                        'allocated_at' => now(),
                    ]);

                    $available -= $toAllocate;

                    // Aktualizace statusu předpisu
                    $newRemaining = $charge->amount_remaining;
                    if ($newRemaining <= 0) {
                        $charge->update(['status' => 'paid']);
                    } elseif ($newRemaining < $charge->amount_total) {
                        $charge->update(['status' => 'partially_paid']);
                    }

                    if ($available <= 0) {
                        break;
                    }
                }
            }
        }
    }
}

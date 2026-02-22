<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ClubIdentifierService;
use Illuminate\Console\Command;

class BackfillClubIdentifiers extends Command
{
    protected $signature = 'club:backfill-identifiers {--regenerate-existing : Regeneruje i u uživatelů, kteří již hodnoty mají}';

    protected $description = 'Doplní uživatelům chybějící club_member_id a payment_vs. Volitelně přegeneruje existující.';

    public function handle(ClubIdentifierService $service): int
    {
        $regenerate = (bool) $this->option('regenerate-existing');

        $query = User::query();

        if (! $regenerate) {
            $query->where(function ($q) {
                $q->whereNull('club_member_id')->orWhereNull('payment_vs');
            });
        }

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $updated = 0;

        $query->chunkById(200, function ($users) use ($service, $regenerate, &$updated, $bar) {
            foreach ($users as $user) {
                $changed = false;

                if ($regenerate || empty($user->club_member_id)) {
                    $user->club_member_id = $service->generateClubMemberId();
                    $changed = true;
                }

                if ($regenerate || empty($user->payment_vs)) {
                    $user->payment_vs = $service->generatePaymentVs();
                    $changed = true;
                }

                if ($changed) {
                    $user->save();
                    $updated++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Aktualizováno uživatelů: {$updated}");

        return self::SUCCESS;
    }
}

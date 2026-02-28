<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ClubIdentifierService;
use Illuminate\Console\Command;

class BackfillClubIdentifiers extends Command
{
    protected $signature = 'club:backfill-identifiers';

    protected $description = 'Doplní uživatelům chybějící club_member_id a payment_vs.';

    public function handle(ClubIdentifierService $service): int
    {
        $query = User::query()->where(function ($q) {
            $q->whereNull('club_member_id')->orWhereNull('payment_vs');
        });

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $updated = 0;

        $query->chunkById(200, function ($users) use ($service, &$updated, $bar) {
            foreach ($users as $user) {
                $changed = false;

                if (empty($user->club_member_id)) {
                    $user->club_member_id = $service->generateClubMemberId();
                    $changed = true;
                }

                if (empty($user->payment_vs)) {
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

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncMemberDefaultTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-member-default-teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje výchozí tým člena na základě jeho příslušnosti k týmu (hráč na soupisce nebo trenér).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::active()->get();
        $count = 0;

        foreach ($users as $user) {
            $teamIds = collect();

            // Týmy kde je hráčem na soupisce
            if ($user->activePlayerProfile) {
                $rosterTeamIds = $user->activePlayerProfile->teams()
                    ->wherePivot('is_on_roster', true)
                    ->pluck('teams.id');
                $teamIds = $teamIds->merge($rosterTeamIds);
            }

            // Týmy kde je trenérem
            $coachTeamIds = $user->teams()->pluck('teams.id');
            $teamIds = $teamIds->merge($coachTeamIds);

            // Unikátní ID týmů
            $uniqueTeamIds = $teamIds->unique();
            $teamCount = $uniqueTeamIds->count();

            $newDefaultTeamId = null;
            $newViewAllByDefault = false;

            if ($teamCount === 1) {
                $newDefaultTeamId = $uniqueTeamIds->first();
                $newViewAllByDefault = false;
            } elseif ($teamCount > 1) {
                $newDefaultTeamId = null;
                $newViewAllByDefault = true;
            }

            // Aktualizace pouze pokud se něco změnilo
            if ($user->member_default_team_id !== $newDefaultTeamId || $user->member_view_all_by_default !== $newViewAllByDefault) {
                $user->update([
                    'member_default_team_id' => $newDefaultTeamId,
                    'member_view_all_by_default' => $newViewAllByDefault,
                ]);
                $count++;
            }
        }

        $this->info("Synchronizace dokončena. Aktualizováno {$count} uživatelů.");
    }
}

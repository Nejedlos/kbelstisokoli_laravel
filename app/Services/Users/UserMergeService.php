<?php

namespace App\Services\Users;

use App\Models\User;
use App\Models\ExternalEntityMapping;
use App\Models\PlayerProfile;
use App\Models\Attendance;
use App\Models\UserConsent;
use App\Models\FinanceCharge;
use App\Models\FinancePayment;
use App\Models\UserSeasonConfig;
use App\Models\UserRelationship;
use App\Models\AuditLog;
use App\Models\StatisticRow;
use App\Services\Stats\Sync\StatisticSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserMergeService
{
    public function __construct(
        protected StatisticSyncService $statisticSyncService
    ) {}

    /**
     * Sloučí zdrojového uživatele do cílového uživatele.
     * Zdrojový uživatel bude následně smazán.
     */
    public function merge(User $source, User $target): void
    {
        if ($source->id === $target->id) {
            return;
        }

        DB::transaction(function () use ($source, $target) {
            // 1. Externí mapování (statistické vazby)
            $source->externalMappings()->update([
                'internal_id' => $target->id,
                'internal_type' => User::class,
            ]);

            // 1.5 Aktualizace řádků statistik
            StatisticRow::where('player_id', $source->id)->update(['player_id' => $target->id]);

            // 2. Hráčské profily
            $source->playerProfiles()->update(['user_id' => $target->id]);

            // 3. Docházka
            $source->attendances()->update(['user_id' => $target->id]);

            // 4. Souhlasy
            $source->consents()->update(['user_id' => $target->id]);

            // 5. Finance (předpisy a platby)
            $source->financeCharges()->update(['user_id' => $target->id]);
            $source->financePayments()->update(['user_id' => $target->id]);

            // 6. Sezónní konfigurace
            // Pozor na duplicity v sezónách - pokud cílový uživatel již konfiguraci pro danou sezónu má,
            // ponecháme tu cílovou a zdrojovou smažeme (nebo ji prostě nepřeneseme).
            foreach ($source->userSeasonConfigs as $config) {
                $exists = UserSeasonConfig::where('user_id', $target->id)
                    ->where('season_id', $config->season_id)
                    ->exists();

                if (!$exists) {
                    $config->update(['user_id' => $target->id]);
                }
            }

            // 7. Role a oprávnění
            foreach ($source->roles as $role) {
                if (!$target->hasRole($role)) {
                    $target->assignRole($role);
                }
            }
            foreach ($source->permissions as $permission) {
                if (!$target->hasPermissionTo($permission)) {
                    $target->givePermissionTo($permission);
                }
            }

            // 8. Rodinné vztahy (children / parents)
            // Převod vazeb, kde je zdrojový uživatel rodičem
            DB::table('user_relationships')
                ->where('parent_id', $source->id)
                ->whereNotExists(function ($query) use ($target) {
                    $query->select(DB::raw(1))
                        ->from('user_relationships as ur2')
                        ->where('ur2.parent_id', $target->id)
                        ->whereRaw('ur2.child_id = user_relationships.child_id');
                })
                ->update(['parent_id' => $target->id]);

            // Převod vazeb, kde je zdrojový uživatel dítětem
            DB::table('user_relationships')
                ->where('child_id', $source->id)
                ->whereNotExists(function ($query) use ($target) {
                    $query->select(DB::raw(1))
                        ->from('user_relationships as ur2')
                        ->where('ur2.child_id', $target->id)
                        ->whereRaw('ur2.parent_id = user_relationships.parent_id');
                })
                ->update(['child_id' => $target->id]);

            // 9. Trenérské týmy (coach_team)
            foreach ($source->teams as $team) {
                if (!$target->teams()->where('teams.id', $team->id)->exists()) {
                    $target->teams()->attach($team->id, [
                        'email' => $team->pivot->email,
                        'phone' => $team->pivot->phone,
                    ]);
                }
            }

            // 10. Audit logy
            AuditLog::where('actor_user_id', $source->id)->update(['actor_user_id' => $target->id]);
            AuditLog::where('subject_type', User::class)->where('subject_id', $source->id)->update(['subject_id' => $target->id]);

            // 11. Smazání zdrojového uživatele
            // Před smazáním odstraníme vazby, které by mohly bránit smazání (pokud nejsou smazány přes cascade)
            $source->roles()->detach();
            $source->permissions()->detach();
            $source->teams()->detach();

            $source->delete();

            // 12. Přepočet statistik pro cílového uživatele
            // Získáme všechny sezóny, ve kterých má cílový uživatel statistiky, a přepočítáme je
            $seasonIds = StatisticRow::where('player_id', $target->id)
                ->pluck('season_id')
                ->filter()
                ->unique();

            foreach ($seasonIds as $seasonId) {
                $this->statisticSyncService->recomputePlayerSummaries($seasonId);
            }
        });
    }
}

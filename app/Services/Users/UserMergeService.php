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
            // Používáme playerProfiles() (HasMany) místo playerProfile (HasOne/active),
            // abychom našli jakýkoliv profil a předešli UniqueConstraintViolation.
            $sourceProfiles = $source->playerProfiles;
            $targetProfile = $target->playerProfiles()->first();

            foreach ($sourceProfiles as $sourceProfile) {
                if ($targetProfile) {
                    // Sloučení do existujícího cílového profilu (nebo prvního převedeného)

                    // Převod týmů z pivot tabulky
                    foreach ($sourceProfile->teams as $team) {
                        $exists = DB::table('player_profile_team')
                            ->where('player_profile_id', $targetProfile->id)
                            ->where('team_id', $team->id)
                            ->exists();

                        if (!$exists) {
                            $targetProfile->teams()->attach($team->id, [
                                'role_in_team' => $team->pivot->role_in_team,
                                'is_primary_team' => false,
                                'is_on_roster' => $team->pivot->is_on_roster,
                                'active_from' => $team->pivot->active_from,
                                'active_to' => $team->pivot->active_to,
                            ]);
                        }
                    }

                    // Převod externích mapování profilu
                    $sourceProfile->externalMappings()->update([
                        'internal_id' => $targetProfile->id,
                        'internal_type' => PlayerProfile::class,
                    ]);

                    // Doplnění chybějících polí
                    $updateData = [];
                    if (empty($targetProfile->jersey_number) && !empty($sourceProfile->jersey_number)) {
                        $updateData['jersey_number'] = $sourceProfile->jersey_number;
                    }
                    if (empty($targetProfile->position) && !empty($sourceProfile->position)) {
                        $updateData['position'] = $sourceProfile->position;
                    }
                    if (!empty($updateData)) {
                        $targetProfile->update($updateData);
                    }

                    $sourceProfile->delete();
                } else {
                    // Cílový uživatel zatím profil nemá, převedeme tento
                    $sourceProfile->update(['user_id' => $target->id]);
                    $targetProfile = $sourceProfile;
                }
            }

            // 3. Docházka
            foreach ($source->attendances as $attendance) {
                $exists = Attendance::where('user_id', $target->id)
                    ->where('attendable_id', $attendance->attendable_id)
                    ->where('attendable_type', $attendance->attendable_type)
                    ->exists();

                if (!$exists) {
                    $attendance->update(['user_id' => $target->id]);
                } else {
                    $attendance->delete();
                }
            }

            // 4. Souhlasy
            foreach ($source->consents as $consent) {
                $exists = UserConsent::where('user_id', $target->id)
                    ->where('consent_type', $consent->consent_type)
                    ->exists();

                if (!$exists) {
                    $consent->update(['user_id' => $target->id]);
                } else {
                    $consent->delete();
                }
            }

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
            // MySQL/MariaDB neumožňuje UPDATE se subquery nad stejnou tabulkou (Error 1093).
            // Proto nejdříve identifikujeme a smažeme vazby, které by po sloučení byly duplicitní,
            // a poté provedeme jednoduchý update.

            // Převod vazeb, kde je zdrojový uživatel rodičem
            $targetChildrenIds = DB::table('user_relationships')
                ->where('parent_id', $target->id)
                ->pluck('child_id');

            $duplicateParentRelationships = DB::table('user_relationships')
                ->where('parent_id', $source->id)
                ->whereIn('child_id', $targetChildrenIds)
                ->pluck('id');

            if ($duplicateParentRelationships->isNotEmpty()) {
                DB::table('user_relationships')->whereIn('id', $duplicateParentRelationships)->delete();
            }

            DB::table('user_relationships')
                ->where('parent_id', $source->id)
                ->update(['parent_id' => $target->id]);

            // Převod vazeb, kde je zdrojový uživatel dítětem
            $targetParentIds = DB::table('user_relationships')
                ->where('child_id', $target->id)
                ->pluck('parent_id');

            $duplicateChildRelationships = DB::table('user_relationships')
                ->where('child_id', $source->id)
                ->whereIn('parent_id', $targetParentIds)
                ->pluck('id');

            if ($duplicateChildRelationships->isNotEmpty()) {
                DB::table('user_relationships')->whereIn('id', $duplicateChildRelationships)->delete();
            }

            DB::table('user_relationships')
                ->where('child_id', $source->id)
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

            // 10.2 Ostatní systémové vazby (vytvořil/nahrál)
            DB::table('finance_charges')->where('created_by_id', $source->id)->update(['created_by_id' => $target->id]);
            DB::table('external_import_runs')->where('created_by_user_id', $source->id)->update(['created_by_user_id' => $target->id]);
            DB::table('legacy_import_batches')->where('created_by_user_id', $source->id)->update(['created_by_user_id' => $target->id]);
            DB::table('redirects')->where('created_by', $source->id)->update(['created_by' => $target->id]);
            DB::table('feedback_reports')->where('user_id', $source->id)->update(['user_id' => $target->id]);
            DB::table('ai_request_logs')->where('user_id', $source->id)->update(['user_id' => $target->id]);
            DB::table('media_assets')->where('uploaded_by_id', $source->id)->update(['uploaded_by_id' => $target->id]);

            // 10.5 Média (Spatie Media Library)
            foreach ($source->media as $media) {
                $media->copy($target, $media->collection_name);
            }

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

    /**
     * Najde reálného uživatele, se kterým by mohl být tento (typicky Ghost) uživatel sloučen.
     */
    public function findMergeTarget(User $user): ?User
    {
        if (!$user->isGhost()) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($user->name));
        if (count($parts) < 2) {
            return null;
        }

        $p1 = $parts[0];
        $p2 = implode(' ', array_slice($parts, 1));

        // Hledáme pouze mezi reálnými uživateli (ne ghosty) se stejným jménem
        $candidates = User::where('id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('email')
                    ->orWhere('email', 'NOT LIKE', 'ghost_%');
            })
            ->where(function ($q) use ($user, $p1, $p2) {
                $q->where('name', $user->name)
                    ->orWhere('name', "{$p2} {$p1}")
                    ->orWhere(fn ($q2) => $q2->where('first_name', $p1)->where('last_name', $p2))
                    ->orWhere(fn ($q2) => $q2->where('first_name', $p2)->where('last_name', $p1));
            })
            ->get();

        // Pokud najdeme právě jednoho kandidáta, považujeme to za shodu
        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        return null;
    }
}

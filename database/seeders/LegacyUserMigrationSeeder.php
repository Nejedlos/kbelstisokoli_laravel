<?php

namespace Database\Seeders;

use App\Enums\BasketballPosition;
use App\Enums\MembershipStatus;
use App\Models\PlayerProfile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;

class LegacyUserMigrationSeeder extends Seeder
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

        // Načtení nových týmů
        $teamC = Team::where('slug', 'muzi-c')->first();
        $teamE = Team::where('slug', 'muzi-e')->first();

        if (! $teamC || ! $teamE) {
            $this->command->error('Nové týmy (muzi-c, muzi-e) nebyly nalezeny v DB. Spusťte nejdříve TeamSeeder.');

            return;
        }

        // Načtení dat ze staré databáze
        try {
            $registrace = DB::connection('old_mysql')->table($oldDb.'.registrace')->get();
            $soupiska = DB::connection('old_mysql')->table($oldDb.'.web_soupiska')->get()->keyBy(function ($item) {
                return trim($item->jmeno);
            });
            // Načtení existujících uživatelů do paměti pro zamezení JSON dotazům
            $existingUsersByLegacyId = User::all()->keyBy(fn ($u) => $u->metadata['legacy_r_id'] ?? null)->forget(null);
        } catch (\Exception $e) {
            $this->command->error('Nepodařilo se načíst data ze staré DB: '.$e->getMessage());

            return;
        }

        $this->command->info('Migrace '.$registrace->count().' uživatelů...');

        $bar = $this->command->getOutput()->createProgressBar($registrace->count());
        $bar->start();

        foreach ($registrace as $reg) {
            // Rozdělení jména (Příjmení Jméno)
            $jmeno = trim($reg->jmeno);
            $jmenoParts = explode(' ', $jmeno, 2);
            $lastName = trim($jmenoParts[0] ?? '');
            $firstName = trim($jmenoParts[1] ?? '');

            // Normalizace telefonu
            $phone = null;
            if ($reg->mobil) {
                try {
                    $cleanPhone = preg_replace('/[^\d+]/', '', $reg->mobil);
                    if ($cleanPhone) {
                        $phone = (string) (new PhoneNumber($cleanPhone, ['CZ']))->formatE164();
                    }
                } catch (\Exception $e) {
                    // Neplatný telefon
                }
            }

            // Nastavení notifikací
            $notificationPreferences = [
                'hromadné_zprávy' => $reg->hrom === 'y',
                'zprávy_o_docházce' => $reg->zpr_doch === 'y',
                'novinky_na_webu' => $reg->novinky === 'y',
            ];

            // Určení stavu členství a aktivity účtu
            $membershipStatus = MembershipStatus::Active;
            $isActive = true;

            if ($reg->zruseno === '1') {
                $isActive = false;
                $membershipStatus = MembershipStatus::Inactive;
            }

            // Párování se soupiskou pro další informace
            $profileData = $soupiska->get($jmeno);
            if ($profileData && $profileData->byvali === 'ano' && $membershipStatus === MembershipStatus::Active) {
                $membershipStatus = MembershipStatus::Former;
                $isActive = false; // Bývalý člen má rovněž neaktivní účet
            }

            // Vytvoření nebo aktualizace uživatele
            $userData = [
                'name' => $jmeno,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'display_name' => $jmeno,
                'phone' => $phone,
                'address_street' => $reg->adresa,
                'is_active' => $isActive,
                'membership_status' => $membershipStatus,
                'notification_preferences' => $notificationPreferences,
                'metadata' => [
                    'legacy_r_id' => $reg->id,
                    'legacy_username' => $reg->user,
                ],
                'email_verified_at' => now(),
            ];

            $user = User::where('email', $reg->email)->first() ?: $existingUsersByLegacyId->get($reg->id);

            if ($user) {
                // Pokud uživatel už existuje (např. byl vytvořen v UserSeeder),
                // aktualizujeme údaje, ale NIKDY nepřepisujeme heslo náhodným.
                $user->update($userData);
            } else {
                // Nový uživatel z migrace - nastavíme mu náhodné heslo
                $userData['password'] = Hash::make(Str::random(16));
                $user = User::create(array_merge(['email' => $reg->email], $userData));
            }

            // Přiřazení role
            if ($reg->admin === '1') {
                if (! $user->hasRole('admin')) {
                    $user->assignRole('admin');
                }
            } else {
                if (! $user->hasRole('player')) {
                    $user->assignRole('player');
                }
            }

            // --- Migrace PlayerProfile ---
            // profileData už máme načtené výše kvůli určení statusu

            // Mapování týmů na základě registrace.team
            $userTeams = [];
            $primaryTeamId = null;

            if ($reg->team == 1) { // CÉČKO
                $userTeams = [$teamC->id];
                $primaryTeamId = $teamC->id;
            } elseif ($reg->team == 2) { // ELITE
                $userTeams = [$teamE->id];
                $primaryTeamId = $teamE->id;
            } elseif ($reg->team == 3) { // VŠICHNI (C+E)
                $userTeams = [$teamC->id, $teamE->id];
                $primaryTeamId = $teamC->id; // Výchozí jako primární
            }

            $profile = $user->playerProfiles()->where('valid_to', null)->first();

            $profileDataToUpdate = [
                'jersey_number' => $profileData->cislo_dresu ?? null,
                'height_cm' => $profileData->vyska ?? null,
                'weight_kg' => $profileData->vaha ?? null,
                'position' => $profileData ? $this->mapPosition($profileData->post) : BasketballPosition::PG,
                'public_bio' => $profileData->charakteristika ?? null,
                'private_note' => $profileData->kariera ?? null,
                'is_active' => $isActive,
                'valid_from' => $reg->cas > 0 ? \Carbon\Carbon::createFromTimestamp($reg->cas) : now(),
                'primary_team_id' => $primaryTeamId,
                'metadata' => array_merge(
                    [
                        'legacy_r_id' => $reg->id,
                        'legacy_team_id' => $reg->team,
                    ],
                    $profileData ? [
                        'legacy_soupiska_id' => $profileData->id,
                        'prezdivka' => $profileData->prezdivka,
                    ] : []
                ),
            ];

            if ($profile) {
                $profile->update($profileDataToUpdate);
            } else {
                $profile = PlayerProfile::create(array_merge(['user_id' => $user->id, 'valid_to' => null], $profileDataToUpdate));
            }

            // Synchronizace s týmy v pivot tabulce
            if (! empty($userTeams)) {
                $syncData = [];
                foreach ($userTeams as $teamId) {
                    $syncData[$teamId] = [
                        'is_primary_team' => $teamId === $primaryTeamId,
                        'role_in_team' => 'player',
                    ];
                }
                $profile->teams()->sync($syncData);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nMigrace uživatelů dokončena.");
    }

    /**
     * Mapování starých pozic na BasketballPosition enum.
     */
    protected function mapPosition(?string $oldPosition): BasketballPosition
    {
        return match (Str::lower($oldPosition)) {
            'rozehravac' => BasketballPosition::PG,
            'kridlo' => BasketballPosition::SF,
            'pivot' => BasketballPosition::C,
            'univerzal' => BasketballPosition::PF,
            default => BasketballPosition::PG, // Fallback
        };
    }
}

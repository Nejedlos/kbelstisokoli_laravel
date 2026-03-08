<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CronTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tasks = [
            [
                'name' => 'Upomínky docházky',
                'command' => 'rsvp:reminders',
                'expression' => '0 9 * * *', // Každý den v 9:00
                'description' => 'Odešle upomínky členům, kteří ještě nepotvrdili účast na akcích v příštích 24h.',
                'priority' => 10,
            ],
            [
                'name' => 'Sync oznámení',
                'command' => 'announcements:sync',
                'expression' => '*/15 * * * *', // Každých 15 minut
                'description' => 'Deaktivuje expirovaná oznámení na webu.',
                'priority' => 5,
            ],
            [
                'name' => 'Synchronizace detailů hráčů (Hluboká)',
                'command' => 'stats:sync-players',
                'expression' => '0 5 * * *', // Každý den v 5:00
                'description' => 'Hloubková synchronizace profilových údajů hráčů (fotky, historie kariéry) z cz.basketball.',
                'priority' => 5,
            ],
            [
                'name' => 'Synchronizace statistik (Celková - vše)',
                'command' => 'stats:import',
                'expression' => '30 3 * * *', // Denně ve 3:30 (baseline)
                'description' => 'Spustí celkovou synchronizaci sezóny (soupisky + všechny zápasy) pro všechny povolené týmy.',
                'priority' => 20,
            ],
            [
                'name' => 'Synchronizace statistik (Průběžná - prioritní)',
                'command' => 'stats:import --recent',
                'expression' => '15 */2 * * *', // Každé 2 hodiny
                'description' => 'Prioritní synchronizace nedávných a právě probíhajících zápasů pro všechny týmy.',
                'priority' => 25,
            ],
            [
                'name' => 'Přepočet sezónních statistik',
                'command' => 'stats:recompute',
                'expression' => '45 */2 * * *', // Každé 2 hodiny (vždy 30min po synchronizaci)
                'description' => 'Přepočítá vypočtené ukazatele (průměry, součty) pro hráče a týmy v aktivní sezóně.',
                'priority' => 15,
            ],
            [
                'name' => 'Synchronizace statistik (Muži C)',
                'command' => 'stats:sync-team-season muzi-c active --recent-days=3',
                'expression' => '0 */2 * * *', // Každé 2 hodiny
                'description' => 'Synchronizuje soupisku a nové zápasy pro Muže C z cz.basketball.',
                'priority' => 0,
            ],
            [
                'name' => 'Synchronizace statistik (Muži E)',
                'command' => 'stats:sync-team-season muzi-e active --recent-days=3',
                'expression' => '10 */2 * * *', // Každé 2 hodiny (s 10min odstupem)
                'description' => 'Synchronizuje soupisku a nové zápasy pro Muže E z cz.basketball.',
                'priority' => 0,
            ],
            [
                'name' => 'Úklid statistik',
                'command' => 'external-stats:cleanup --days=30',
                'expression' => '0 4 * * *', // Každý den ve 4:00 ráno
                'description' => 'Promaže staré HTML snapshoty a historii běhů externích importů.',
                'priority' => -5,
            ],
            [
                'name' => 'Systémový úklid',
                'command' => 'system:cleanup',
                'expression' => '0 3 * * *', // Každý den ve 3:00 ráno
                'description' => 'Provede promazání starých logů a dočasných souborů.',
                'priority' => -10,
            ],
            [
                'name' => 'Synchronizace financí',
                'command' => 'finance:sync',
                'expression' => '0 1 * * *', // Každý den v 1:00
                'description' => 'Kontroluje splatnost předpisů a aktualizuje jejich statusy.',
                'priority' => 10,
            ],
        ];

        foreach ($tasks as $task) {
            \App\Models\CronTask::updateOrCreate(
                ['command' => $task['command']],
                $task
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'name' => 'RSVP Upomínky',
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
                'name' => 'Import statistik',
                'command' => 'stats:import',
                'expression' => '0 */2 * * *', // Každé 2 hodiny
                'description' => 'Spustí pipeline pro import externích sportovních statistik.',
                'priority' => 0,
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

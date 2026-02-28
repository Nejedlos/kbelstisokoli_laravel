<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:cleanup {--force : Vynutit bez potvrzení}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bezpečně vymaže všechna finanční data (předpisy, platby, alokace)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('production') && ! $this->option('force')) {
            if (! $this->confirm('Opravdu chcete smazat VŠECHNA FINANČNÍ DATA na produkci? Tato akce je nevratná!', false)) {
                $this->info('Akce zrušena.');

                return self::SUCCESS;
            }
        }

        $this->info('Čistím finanční data...');

        Schema::disableForeignKeyConstraints();

        $tables = [
            'charge_payment_allocations',
            'finance_payments',
            'finance_charges',
        ];

        foreach ($tables as $table) {
            $this->line("- Čištění tabulky: <comment>{$table}</comment>");
            DB::table($table)->delete();

            // Resetování auto-incrementu pro MySQL
            if (config('database.default') === 'mysql') {
                $prefix = DB::getTablePrefix();
                DB::statement("ALTER TABLE `{$prefix}{$table}` AUTO_INCREMENT = 1");
            }
        }

        Schema::enableForeignKeyConstraints();

        $this->info('Finanční data byla úspěšně vymazána.');

        if (! $this->option('force') && $this->confirm('Chcete nyní spustit synchronizaci statusů (finance:sync)?', true)) {
            $this->call('finance:sync');
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PageCacheClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Smaže veškerou full-page cache pro veřejný web';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mazání full-page cache...');

        $store = config('cache.default');
        $prefix = config('cache.prefix', '');
        $fullPagePrefix = $prefix . 'full_page_';

        // 1. Pokud používáme databázi, můžeme cíleně smazat jen full-page záznamy
        if ($store === 'database') {
            try {
                $table = config('cache.stores.database.table', 'cache');
                $deleted = DB::table($table)
                    ->where('key', 'like', $fullPagePrefix . '%')
                    ->delete();

                $this->info("Smazáno $deleted záznamů z databázové cache.");
            } catch (\Throwable $e) {
                $this->error('Chyba při mazání z DB: ' . $e->getMessage());
            }
        }
        // 2. Pro Redis můžeme také mazat podle prefixu
        elseif ($store === 'redis') {
            try {
                $redis = \Illuminate\Support\Facades\Redis::connection();
                $keys = $redis->keys($fullPagePrefix . '*');

                if (!empty($keys)) {
                    // Redis prefixy mohou být různé v závislosti na konfiguraci,
                    // ale standardní Laravel Redis store prefixuje klíče.
                    foreach ($keys as $key) {
                        $redis->del($key);
                    }
                    $this->info("Smazáno " . count($keys) . " klíčů z Redis cache.");
                } else {
                    $this->info("V Redis nenalezeny žádné full-page cache klíče.");
                }
            } catch (\Throwable $e) {
                $this->error('Chyba při mazání z Redis: ' . $e->getMessage());
            }
        }
        // 3. Pro file driver na SDÍLENÉM hostingu (Webglobe) nemáme prefix-based clear.
        // Standardní cache:clear smaže vše, což je součástí optimize:clear.
        else {
            $this->comment("Pro driver '$store' nelze smazat cache selektivně podle prefixu bez prohledávání celého úložiště.");
            $this->comment('Doporučujeme použít php artisan cache:clear pro úplné promazání.');
        }

        $this->info('Hotovo.');

        return 0;
    }
}

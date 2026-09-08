<?php

namespace App\Observers;

use App\Models\BasketballMatch;
use App\Models\ExternalPlayerMatch;
use App\Models\Page;
use App\Models\Partner;
use App\Models\PhotoPool;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\StatisticRow;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceObserver
{
    /**
     * Handle the model "saved" event.
     */
    public function saved($model): void
    {
        // Pokud je to technická změna PhotoPoolu, ignorujeme
        if ($model instanceof PhotoPool) {
            $technicalFields = ['is_processing_import', 'pending_import_queue', 'updated_at'];
            $dirtyFields = array_keys($model->getDirty());

            if (! empty($dirtyFields) && empty(array_diff($dirtyFields, $technicalFields))) {
                return;
            }
        }

        $this->clearCache($model);
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted($model): void
    {
        $this->clearCache($model);
    }

    protected function clearCache($model = null): void
    {
        try {
            // Základní klíče mažeme vždy (jsou malé)
            Cache::forget('performance_settings');
            Cache::forget('view_composer_data_cs');
            Cache::forget('view_composer_data_en');
            Cache::forget('global_branding_settings_cs');
            Cache::forget('global_branding_settings_en');

            // Selektivní mazání podle typu modelu
            if ($model instanceof Partner) {
                Cache::forget('partners_homepage_strip');
                Cache::forget('partners_footer');
                Cache::forget('partners_match');
                Cache::forget('partners_contact');
                Cache::forget('partners_recruitment');
            }

            // Statistiky mažeme pouze pokud se změní zápasy nebo statistiky
            $shouldClearStats = ! $model ||
                $model instanceof BasketballMatch ||
                $model instanceof StatisticRow ||
                $model instanceof ExternalPlayerMatch;

            // Fragmenty mažeme u obsahu
            $shouldClearFragments = ! $model ||
                $model instanceof Post ||
                $model instanceof Page ||
                $model instanceof Team ||
                $model instanceof PostCategory;

            // 2. Pro fragmenty a full-page cache
            if (config('cache.default') === 'database') {
                $table = config('cache.stores.database.table', 'cache');
                $prefix = config('cache.prefix', '');

                $query = DB::table($table)->where('key', 'like', $prefix.'full_page_%');

                if ($shouldClearFragments) {
                    $query->orWhere('key', 'like', $prefix.'fragment_%')
                        ->orWhere('key', 'like', $prefix.'help_%');
                }

                if ($shouldClearStats) {
                    $query->orWhere('key', 'like', $prefix.'team_stats_%')
                        ->orWhere('key', 'like', $prefix.'player_stats_%');
                }

                $query->delete();
            }
        } catch (\Throwable $e) {
            // Tichá chyba
        }
    }
}

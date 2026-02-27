<?php

namespace App\Observers;

use App\Services\PerformanceService;
use Illuminate\Support\Facades\Cache;

class PerformanceObserver
{
    /**
     * Handle the model "saved" event.
     */
    public function saved($model): void
    {
        $this->clearCache();
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted($model): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        // Mažeme fragment cache a full-page cache
        Cache::flush(); // Pro jistotu mažeme vše, fragmenty i full-page

        // Pokud chceme být selektivní, můžeme použít tagy (pokud je driver podporuje)
        // Ale Cache::flush() je nejjistější cesta k aktuálnímu obsahu.
    }
}

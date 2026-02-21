<?php

namespace App\Jobs;

use App\Models\FinanceCharge;
use App\Services\Finance\FinanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FinanceSyncJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(FinanceService $financeService): void
    {
        Log::info('Spouštím synchronizaci financí...');

        // 1. Najít všechny, které by měly být overdue, ale nejsou
        $toOverdue = FinanceCharge::whereIn('status', ['open', 'partially_paid'])
            ->where('due_date', '<', now()->startOfDay())
            ->get();

        foreach ($toOverdue as $charge) {
            $financeService->syncChargeStatus($charge);
        }

        Log::info("Synchronizace financí dokončena. Aktualizováno {$toOverdue->count()} předpisů.");
    }
}

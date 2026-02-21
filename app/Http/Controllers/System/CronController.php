<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    /**
     * Spustí Laravel scheduler přes webový požadavek.
     */
    public function run(Request $request)
    {
        $token = config('system.cron.token');

        if (!$token || $request->get('token') !== $token) {
            Log::warning('Unauthorized cron run attempt from IP: ' . $request->ip());
            return response()->json(['status' => 'unauthorized'], 401);
        }

        try {
            // Spuštění scheduleru
            Artisan::call('schedule:run');
            $output = Artisan::output();

            return response()->json([
                'status' => 'success',
                'message' => 'Scheduler finished.',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            Log::error('Web-cron failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

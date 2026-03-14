<?php

namespace App\Livewire;

use App\Models\ExternalImportRun;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SyncStatusBar extends Component
{
    public bool $isCollapsed = true;

    public function render()
    {
        $runs = collect();

        if (Auth::check() && Auth::user()->can('manage_stats')) {
            $runs = ExternalImportRun::where('status', 'running')
                ->whereIn('run_type', ['player_sync_batch', 'player_sync_excesive', 'player_sync_all', 'team_sync_season'])
                ->orderByDesc('started_at')
                ->get();
        }

        return view('livewire.sync-status-bar', [
            'runs' => $runs,
        ]);
    }

    public function refresh()
    {
        // Tato metoda je volána z frontend přes $wire.$refresh() nebo polling
        // Livewire automaticky zavolá render()
    }

    public function cancelRun(int $runId)
    {
        if (Auth::check() && Auth::user()->can('manage_stats')) {
            $run = ExternalImportRun::find($runId);
            if ($run && $run->status === 'running') {
                $run->cancel();
                $this->dispatch('sync-cancelled', runId: $runId);
            }
        }
    }
}

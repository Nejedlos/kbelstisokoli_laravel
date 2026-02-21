<?php

namespace App\Services\Communication;

use App\Models\Announcement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CommunicationService
{
    /**
     * Získá aktivní oznámení pro dané publikum.
     */
    public function getActiveAnnouncements(string $audience = 'public'): Collection
    {
        $cacheKey = "announcements_{$audience}";

        return Cache::remember($cacheKey, 300, function () use ($audience) {
            return Announcement::active()
                ->forAudience($audience)
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Vymaže cache oznámení.
     */
    public function clearAnnouncementCache(): void
    {
        Cache::forget('announcements_public');
        Cache::forget('announcements_member');
        Cache::forget('announcements_both');
    }
}

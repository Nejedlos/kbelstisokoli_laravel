<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ClubIdentifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateUserIdentifiersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $userId) {}

    /**
     * Execute the job.
     */
    public function handle(ClubIdentifierService $service): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $dirty = false;

        if (empty($user->club_member_id)) {
            $user->club_member_id = $service->generateClubMemberId();
            $dirty = true;
        }

        if (empty($user->payment_vs)) {
            $user->payment_vs = $service->generatePaymentVs();
            $dirty = true;
        }

        if ($dirty) {
            $user->save();
        }
    }
}

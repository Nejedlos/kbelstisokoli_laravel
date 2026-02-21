<?php

namespace App\Listeners;

use App\Events\FinanceChargeCreated;
use App\Notifications\NewChargeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFinanceNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(FinanceChargeCreated $event): void
    {
        $user = $event->charge->user;

        if ($user && $event->charge->is_visible_to_member) {
            $user->notify(new NewChargeNotification($event->charge));
        }
    }
}

<?php

namespace App\Events;

use App\Models\FinanceCharge;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FinanceChargeCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public FinanceCharge $charge) {}
}

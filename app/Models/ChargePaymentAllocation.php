<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargePaymentAllocation extends Model
{
    protected $fillable = [
        'finance_charge_id',
        'finance_payment_id',
        'amount',
        'allocated_at',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    public function charge(): BelongsTo
    {
        return $this->belongsTo(FinanceCharge::class, 'finance_charge_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(FinancePayment::class, 'finance_payment_id');
    }
}

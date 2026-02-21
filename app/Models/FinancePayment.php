<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FinancePayment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'paid_at',
        'payment_method',
        'variable_symbol',
        'transaction_reference',
        'source_note',
        'status',
        'recorded_by_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ChargePaymentAllocation::class);
    }

    public function charges(): BelongsToMany
    {
        return $this->belongsToMany(FinanceCharge::class, 'charge_payment_allocations')
            ->withPivot(['amount', 'allocated_at', 'note'])
            ->withTimestamps();
    }

    /**
     * Celková alokovaná (využitá) částka z této platby.
     */
    public function getAmountAllocatedAttribute(): float
    {
        return (float) $this->allocations()->sum('amount');
    }

    /**
     * Zbývající volná částka z této platby k alokaci.
     */
    public function getAmountAvailableAttribute(): float
    {
        return (float) $this->amount - $this->getAmountAllocatedAttribute();
    }
}

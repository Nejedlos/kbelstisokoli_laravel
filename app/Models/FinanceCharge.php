<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FinanceCharge extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'charge_type',
        'amount_total',
        'currency',
        'due_date',
        'period_from',
        'period_to',
        'status',
        'is_visible_to_member',
        'notes_internal',
        'created_by_id',
    ];

    protected $casts = [
        'amount_total' => 'decimal:2',
        'due_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'is_visible_to_member' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ChargePaymentAllocation::class);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(FinancePayment::class, 'charge_payment_allocations')
            ->withPivot(['amount', 'allocated_at', 'note'])
            ->withTimestamps();
    }

    /**
     * Celková zaplacená částka (suma alokací).
     */
    public function getAmountPaidAttribute(): float
    {
        return (float) $this->allocations()->sum('amount');
    }

    /**
     * Zbývající částka k úhradě.
     */
    public function getAmountRemainingAttribute(): float
    {
        return (float) $this->amount_total - $this->getAmountPaidAttribute();
    }

    /**
     * Je předpis po splatnosti?
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'paid' || $this->status === 'cancelled') {
            return false;
        }

        return $this->due_date && $this->due_date->isPast() && $this->getAmountRemainingAttribute() > 0;
    }
}

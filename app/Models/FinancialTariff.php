<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTariff extends Model
{
    protected $fillable = [
        'name',
        'base_amount',
        'unit',
        'type',
        'prepaid_events_count',
        'extra_event_amount',
        'installment_plan',
        'calculate_attendance_fines',
        'calculate_th_fines',
        'description',
        'metadata',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'prepaid_events_count' => 'integer',
        'extra_event_amount' => 'decimal:2',
        'installment_plan' => 'array',
        'calculate_attendance_fines' => 'boolean',
        'calculate_th_fines' => 'boolean',
        'metadata' => 'array',
    ];

    public function userSeasonConfigs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSeasonConfig::class);
    }
}

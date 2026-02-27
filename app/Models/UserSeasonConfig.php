<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSeasonConfig extends Model
{
    protected $fillable = [
        'user_id',
        'season_id',
        'financial_tariff_id',
        'billing_start_month',
        'billing_end_month',
        'exemption_start_month',
        'exemption_end_month',
        'track_attendance',
        'opening_balance',
        'metadata',
    ];

    protected $casts = [
        'track_attendance' => 'boolean',
        'opening_balance' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function tariff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinancialTariff::class, 'financial_tariff_id');
    }
}

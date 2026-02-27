<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTariff extends Model
{
    protected $fillable = [
        'name',
        'base_amount',
        'unit',
        'description',
        'metadata',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function userSeasonConfigs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSeasonConfig::class);
    }
}

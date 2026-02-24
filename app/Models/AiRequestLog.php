<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequestLog extends Model
{
    protected $table = 'ai_request_logs';

    protected $fillable = [
        'user_id',
        'context',
        'provider',
        'model',
        'status',
        'prompt_preview',
        'response_preview',
        'latency_ms',
        'token_usage',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'token_usage' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $table = 'ai_settings';

    protected $fillable = [
        'enabled',
        'use_database_settings',
        'provider',
        'openai_api_key',
        'openai_base_url',
        'openai_organization',
        'openai_project',
        'openai_timeout_seconds',
        'openai_max_retries',
        'openai_verify_ssl',
        'default_chat_model',
        'analyze_model',
        'fast_model',
        'embeddings_model',
        'model_presets',
        'temperature',
        'top_p',
        'max_output_tokens',
        'system_prompt_default',
        'system_prompt_search',
        'cache_enabled',
        'cache_ttl_seconds',
        'debug_enabled',
        'debug_log_requests',
        'debug_log_responses',
        'debug_log_to_database',
        'retention_days',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'use_database_settings' => 'boolean',
            'openai_api_key' => 'encrypted',
            'openai_verify_ssl' => 'boolean',
            'openai_timeout_seconds' => 'integer',
            'openai_max_retries' => 'integer',
            'model_presets' => 'array',
            'temperature' => 'float',
            'top_p' => 'float',
            'max_output_tokens' => 'integer',
            'cache_enabled' => 'boolean',
            'cache_ttl_seconds' => 'integer',
            'debug_enabled' => 'boolean',
            'debug_log_requests' => 'boolean',
            'debug_log_responses' => 'boolean',
            'debug_log_to_database' => 'boolean',
            'retention_days' => 'integer',
        ];
    }
}

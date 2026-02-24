<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Models\AiRequestLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class AiSettingsService
{
    protected const CACHE_KEY = 'ai_global_settings';
    protected const CACHE_TTL = 3600;

    /**
     * Získá aktuální AI nastavení (DB s fallbackem na config/env).
     */
    public function getSettings(): array
    {
        $config = config('ai');
        $dbSettings = $this->getDbSettings();

        // Priorita 1: Nastavení v DB (pokud existuje a je v něm zapnuto use_database_settings)
        if ($dbSettings && ($dbSettings['use_database_settings'] ?? false)) {
            return $dbSettings;
        }

        // Priorita 2: Pokud je používání DB vynuceno v configu, ale záznam v DB neexistuje,
        // vrátíme sice config, ale se zapnutým use_database_settings pro UI
        if ($config['use_database_settings'] && $dbSettings) {
             return $dbSettings;
        }

        // Fallback: Data z config/env
        return $this->formatFromConfig($config);
    }

    /**
     * Načte nastavení z DB s využitím cache.
     */
    protected function getDbSettings(): ?array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                if (!Schema::hasTable('ai_settings')) {
                    return null;
                }

                $setting = AiSetting::first();
                if (!$setting) {
                    return null;
                }

                return $setting->toArray();
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /**
     * Formátuje nastavení z Laravel configu (fallback).
     */
    protected function formatFromConfig(array $cfg): array
    {
        return [
            'enabled' => $cfg['enabled'],
            'use_database_settings' => $cfg['use_database_settings'],
            'provider' => 'openai',
            'openai_api_key' => $cfg['openai']['api_key'],
            'openai_base_url' => $cfg['openai']['base_url'],
            'openai_organization' => $cfg['openai']['organization'],
            'openai_project' => $cfg['openai']['project'],
            'openai_timeout_seconds' => $cfg['openai']['timeout'],
            'openai_max_retries' => $cfg['openai']['max_retries'],
            'openai_verify_ssl' => true,
            'default_chat_model' => $cfg['openai']['models']['default'],
            'analyze_model' => $cfg['openai']['models']['analyze'],
            'fast_model' => $cfg['openai']['models']['fast'],
            'embeddings_model' => $cfg['openai']['models']['embeddings'],
            'model_presets' => null,
            'temperature' => $cfg['defaults']['temperature'],
            'top_p' => $cfg['defaults']['top_p'],
            'max_output_tokens' => $cfg['defaults']['max_tokens'],
            'system_prompt_default' => null,
            'system_prompt_search' => null,
            'cache_enabled' => true,
            'cache_ttl_seconds' => $cfg['openai']['cache_ttl'],
            'debug_enabled' => config('app.debug', false),
            'debug_log_requests' => false,
            'debug_log_responses' => false,
            'debug_log_to_database' => false,
            'retention_days' => 30,
        ];
    }

    /**
     * Vymaže cache nastavení.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Zalituje požadavek do DB (pokud je to zapnuto).
     */
    public function logRequest(array $data): void
    {
        $settings = $this->getSettings();

        if (!($settings['debug_log_to_database'] ?? false)) {
            return;
        }

        try {
            AiRequestLog::create(array_merge([
                'user_id' => Auth::id(),
                'provider' => $settings['provider'] ?? 'openai',
                'created_at' => now(),
            ], $data));
        } catch (\Throwable $e) {
            // Logování by nemělo shodit aplikaci
            \Illuminate\Support\Facades\Log::error('AI Log Error: ' . $e->getMessage());
        }
    }

    /**
     * Získá instanci pro testování OpenAI připojení.
     */
    public function getTestConfig(): array
    {
        // Vždy načteme nejaktuálnější nastavení (bez cache pro testy)
        $this->clearCache();
        return $this->getSettings();
    }
}

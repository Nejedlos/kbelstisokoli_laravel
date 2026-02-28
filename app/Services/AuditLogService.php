<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class AuditLogService
{
    /**
     * Logování obecné události.
     */
    public function log(
        string $eventKey,
        string $category,
        string $action,
        ?Model $subject = null,
        array $metadata = [],
        array $changes = [],
        string $severity = 'info',
        ?string $subjectLabel = null
    ): AuditLog {
        $user = Auth::user();
        $ip = Request::ip();
        $isAuth = $category === 'auth';

        return AuditLog::create([
            'occurred_at' => now(),
            'category' => $category,
            'event_key' => $eventKey,
            'action' => $action,
            'severity' => $severity,
            'actor_user_id' => $user?->id,
            'actor_type' => $user ? get_class($user) : null,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'subject_label' => $subjectLabel ?? ($isAuth ? null : $this->resolveSubjectLabel($subject)),
            'route_name' => Request::route()?->getName(),
            'url' => Str::limit(Request::fullUrl(), 500),
            'ip_address' => $this->anonymizeIp($ip),
            'ip_hash' => $ip ? hash('sha256', $ip.config('app.key')) : null,
            'user_agent_summary' => Str::limit(Request::userAgent(), 255),
            'request_id' => Request::header('X-Request-ID') ?? (string) Str::uuid(),
            'metadata' => $metadata,
            'changes' => $changes,
            'is_system_event' => $isAuth ? ($user === null) : $this->resolveIsSystemEvent(),
            'source' => $isAuth ? 'web' : $this->resolveSource(),
        ]);
    }

    /**
     * Helper pro logování auth událostí.
     */
    public function security(string $eventKey, string $action, array $metadata = [], string $severity = 'info'): AuditLog
    {
        return $this->log(
            eventKey: "auth.{$eventKey}",
            category: 'auth',
            action: $action,
            metadata: $metadata,
            severity: $severity
        );
    }

    /**
     * Helper pro logování CRUD událostí.
     */
    public function crud(Model $subject, string $action, array $changes = [], array $metadata = []): AuditLog
    {
        $className = strtolower(class_basename($subject));

        return $this->log(
            eventKey: "{$className}.{$action}",
            category: 'admin_crud',
            action: $action,
            subject: $subject,
            metadata: $metadata,
            changes: $changes,
            severity: $action === 'deleted' ? 'warning' : 'info'
        );
    }

    /**
     * Anonymizace IP adresy pro zachování soukromí.
     */
    protected function anonymizeIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Nahradí poslední část IP za nulu: 192.168.1.5 -> 192.168.1.0
            return preg_replace('/\.[0-9]+$/', '.0', $ip);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Oseká IPv6: 2001:0db8:85a3:0000:0000:8a2e:0370:7334 -> 2001:0db8:85a3:0000:0000:8a2e:0370:0000
            $parts = explode(':', $ip);
            if (count($parts) > 1) {
                array_pop($parts);

                return implode(':', $parts).':0000';
            }
        }

        return $ip;
    }

    /**
     * Inteligentní řešení lidsky čitelného labelu pro model.
     */
    protected function resolveSubjectLabel(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        $attributes = ['name', 'title', 'label', 'headline', 'email', 'username'];
        foreach ($attributes as $attr) {
            if (isset($subject->{$attr})) {
                $val = $subject->{$attr};
                if (is_array($val)) {
                    $lang = app()->getLocale();

                    return $val[$lang] ?? reset($val);
                }

                return (string) $val;
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }

    /**
     * Rozlišení zdroje akce.
     */
    protected function resolveSource(): string
    {
        if (app()->runningInConsole()) {
            return 'console';
        }

        if (Request::is('admin*')) {
            return 'admin';
        }

        if (Request::is('api*')) {
            return 'api';
        }

        return 'web';
    }

    /**
     * Detekce, zda jde o systémovou událost.
     */
    protected function resolveIsSystemEvent(): bool
    {
        return app()->runningInConsole() || ! Auth::check();
    }
}

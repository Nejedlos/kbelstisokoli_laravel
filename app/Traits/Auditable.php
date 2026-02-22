<?php

namespace App\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Pole, která se nemají logovat (citlivá data).
     */
    protected function getAuditIgnoreFields(): array
    {
        return [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
            'updated_at'
        ];
    }

    /**
     * Inicializace traitu a registrace model events.
     */
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $ignoreFields = $model->getAuditIgnoreFields();
            $changes = [];

            foreach ($model->getAttributes() as $key => $value) {
                if (in_array($key, $ignoreFields)) continue;
                $changes[$key] = ['new' => $value];
            }

            app(AuditLogService::class)->crud($model, 'created', $changes);
        });

        static::updated(function (Model $model) {
            $ignoreFields = $model->getAuditIgnoreFields();
            $changes = [];

            foreach ($model->getChanges() as $key => $newValue) {
                if (in_array($key, $ignoreFields)) continue;

                $changes[$key] = [
                    'old' => $model->getOriginal($key),
                    'new' => $newValue,
                ];
            }

            if (!empty($changes)) {
                app(AuditLogService::class)->crud($model, 'updated', $changes);
            }
        });

        static::deleted(function (Model $model) {
            $ignoreFields = $model->getAuditIgnoreFields();
            $data = [];

            foreach ($model->getAttributes() as $key => $value) {
                if (in_array($key, $ignoreFields)) continue;
                $data[$key] = $value;
            }

            app(AuditLogService::class)->crud($model, 'deleted', [], ['deleted_snapshot' => $data]);
        });
    }
}

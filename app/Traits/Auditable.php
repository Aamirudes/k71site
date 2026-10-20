<?php

namespace App\Traits;

use App\Models\AuditLog;

/**
 * Trait for models that should be audited.
 * Logs create, update, delete actions with old/new values.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAudit($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }

            $old = array_intersect_key($model->getOriginal(), $dirty);
            static::logAudit($model, 'updated', $old, $dirty);
        });

        static::deleted(function ($model) {
            static::logAudit($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function logAudit($model, string $action, ?array $oldValues, ?array $newValues): void
    {
        // Don't log during seeding or testing unless explicitly enabled
        if (app()->runningInConsole() && !config('marketplace.audit_in_console', false)) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'action' => $action,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable) {
            // Silently fail audit logging; never break the main operation
        }
    }
}

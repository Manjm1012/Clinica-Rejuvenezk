<?php

namespace App\Models\Concerns;

use App\Support\Audit;

/**
 * Registra en audit_logs la creación y modificación del modelo (solo nombres de campos).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => Audit::log('created', $model));

        static::updated(function ($model): void {
            $fields = array_values(array_diff(array_keys($model->getChanges()), ['updated_at', 'remember_token']));

            if ($fields !== []) {
                Audit::log('updated', $model, $fields);
            }
        });
    }
}

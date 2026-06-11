<?php

namespace App\Models\Concerns;

use App\Models\Clinic;
use App\Support\CurrentClinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToClinic
{
    public static function bootBelongsToClinic(): void
    {
        static::addGlobalScope('clinic', function (Builder $builder): void {
            if (app()->runningInConsole() || ! app()->bound(CurrentClinic::class)) {
                return;
            }

            $clinicId = app(CurrentClinic::class)->id();
            if ($clinicId) {
                $builder->where($builder->getModel()->getTable() . '.clinic_id', $clinicId);
            }
        });

        static::creating(function ($model): void {
            if (! app()->bound(CurrentClinic::class)) {
                return;
            }

            if (! $model->clinic_id) {
                $model->clinic_id = app(CurrentClinic::class)->id();
            }
        });
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}

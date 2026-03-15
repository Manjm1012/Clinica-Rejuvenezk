<?php

namespace App\Traits;

use App\Scopes\TenantScope;

trait HasTenant
{
    public static function bootHasTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            $tenant = app('tenant');
            if ($tenant && empty($model->client_id)) {
                $model->client_id = $tenant->id;
            }
        });
    }
}

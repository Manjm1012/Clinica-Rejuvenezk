<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $tenant = app('tenant');

        if ($tenant && isset($model->client_id)) {
            $builder->where($model->getTable() . '.client_id', $tenant->id);
        }
    }
}

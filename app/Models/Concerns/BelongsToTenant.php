<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && Auth::user()->tenant_id) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    Auth::user()->tenant_id
                );
            }
        });

        static::creating(function (Model $model) {
            if (
                Auth::check()
                && Auth::user()->tenant_id
                && empty($model->tenant_id)
            ) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

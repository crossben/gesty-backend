<?php

namespace App\Traits;

use App\Services\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasSchoolScope
{
    protected static function bootHasSchoolScope()
    {
        static::addGlobalScope('school', function (Builder $builder) {
            $tenantManager = app(TenantManager::class);
            $schoolId = $tenantManager->getSchoolId();

            if ($schoolId) {
                // If we have a school from subdomain, prioritize it
                $builder->where($builder->getModel()->getTable() . '.school_id', $schoolId);
                
                // Security check if also logged in
                if (Auth::check() && Auth::user()->school_id !== $schoolId) {
                    abort(403, "Unauthorized tenant access.");
                }
            } elseif (Auth::check() && Auth::user()->school_id) {
                // Fallback to Auth user if no subdomain but logged in (e.g. central login)
                $builder->where($builder->getModel()->getTable() . '.school_id', Auth::user()->school_id);
            }
        });

        static::creating(function ($model) {
            $tenantManager = app(TenantManager::class);
            $schoolId = $tenantManager->getSchoolId();

            if ($schoolId && !$model->school_id) {
                $model->school_id = $schoolId;
            } elseif (Auth::check() && Auth::user()->school_id && !$model->school_id) {
                $model->school_id = Auth::user()->school_id;
            }
        });
    }
}

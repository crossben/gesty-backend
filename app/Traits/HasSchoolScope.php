<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasSchoolScope
{
    protected static function bootHasSchoolScope()
    {
        static::addGlobalScope('school', function (Builder $builder) {
            if (Auth::check() && Auth::user()->school_id) {
                $builder->where($builder->getModel()->getTable() . '.school_id', Auth::user()->school_id);
            }
        });

        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->school_id && !$model->school_id) {
                $model->school_id = Auth::user()->school_id;
            }
        });
    }
}

<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory, UseUuid, HasSchoolScope;

    protected $fillable = [
        'school_id',
        'class_id',
        'subject',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'teacher',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}

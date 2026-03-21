<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIReport extends Model
{
    use HasFactory, UseUuid, HasSchoolScope;

    protected $table = 'ai_reports';

    protected $fillable = [
        'school_id',
        'class_id',
        'type',
        'report',
        'recommendations',
    ];

    protected $casts = [
        'report' => 'array',
        'recommendations' => 'array',
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

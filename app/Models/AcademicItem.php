<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicItem extends Model
{
    use HasFactory, UseUuid, HasSchoolScope;

    protected $fillable = [
        'school_id',
        'class_id',
        'type',
        'subject',
        'title',
        'description',
        'due_date',
        'max_score',
        'difficulty',
        'status',
        'is_ai_generated',
        'ai_content',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'max_score' => 'decimal:2',
        'difficulty' => 'string',
        'status' => 'string',
        'is_ai_generated' => 'boolean',
        'ai_content' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}

<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory, UseUuid, HasSchoolScope;

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_item_id',
        'score',
        'max_score',
        'comments',
        'graded_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicItem()
    {
        return $this->belongsTo(AcademicItem::class);
    }
}

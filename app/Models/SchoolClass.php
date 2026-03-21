<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory, UseUuid, HasSchoolScope;

    protected $fillable = ['school_id', 'name', 'level', 'description'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function academicItems()
    {
        return $this->hasMany(AcademicItem::class, 'class_id');
    }
}

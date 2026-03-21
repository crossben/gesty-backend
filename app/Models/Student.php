<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, UseUuid, HasSchoolScope;

    protected $fillable = ['school_id', 'class_id', 'name', 'email', 'phone', 'birth_date'];

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

<?php

namespace App\Models;

use App\Traits\HasSchoolScope;
use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Student extends Model
{
    use HasFactory, UseUuid, HasSchoolScope, HasApiTokens;

    protected $fillable = [
        'school_id',
        'class_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'matricule',
        'gender',
        'date_of_birth',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'password_changed_at' => 'datetime',
        ];
    }

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

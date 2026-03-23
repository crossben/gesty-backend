<?php

namespace App\Models;

use App\Traits\UseUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory, UseUuid;

    protected $fillable = ['name', 'slug', 'address', 'phone', 'email', 'website'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($school) {
            if (!$school->slug) {
                $school->slug = \Illuminate\Support\Str::slug($school->name);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
}

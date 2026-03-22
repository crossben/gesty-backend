<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UseUuid;
use App\Traits\HasSchoolScope;

class Announcement extends Model
{
    use HasFactory, UseUuid, HasSchoolScope;

    protected $fillable = [
        'school_id',
        'author_id',
        'class_id',
        'title',
        'content',
        'priority',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}

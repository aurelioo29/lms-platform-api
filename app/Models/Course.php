<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [

        'title',
        'slug',
        'description',
        'status',
        'enroll_key_hash',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // admin Creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // students enrolled
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot(['status', 'method', 'enrolled_at', 'enrolled_by'])
            ->withTimestamps();
    }

    public function instructors()
    {
        return $this->hasMany(CourseInstructor::class);
    }
}

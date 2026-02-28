<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'status', 'created_by', 'published_at',
        'enroll_key_hash', 'enroll_key_enc', 'enroll_key_plain',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = ['courseInstructors'];

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

    public function courseInstructors()
    {
        return $this->hasMany(\App\Models\CourseInstructor::class);
    }

    public function getCourseInstructorsAttribute()
    {
        // Pastikan relation snake_case sudah diload dulu
        return $this->course_instructors ?? $this->courseInstructors()->with('instructor:id,name')->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseInstructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'role',
        'assigned_by',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    // who assigned this instructor
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

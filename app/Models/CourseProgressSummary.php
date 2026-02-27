<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseProgressSummary extends Model
{
    protected $table = 'course_progress_summaries';

    public $timestamps = false;

    protected $fillable = [
        'course_id',
        'user_id',
        'completion_percent',
        'completed_lessons_count',
        'total_lessons_count',
        'updated_at',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

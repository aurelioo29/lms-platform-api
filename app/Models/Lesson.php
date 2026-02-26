<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'content_json',
        'content_type',
        'sort_order',
        'created_by',
        'published_at',
        'unlock_after_lesson_id',
        'lock_mode',
    ];

    protected $casts = [
        'content_json' => 'array',
        'published_at' => 'datetime',
    ];

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function unlockAfter()
    {
        return $this->belongsTo(Lesson::class, 'unlock_after_lesson_id');
    }
}

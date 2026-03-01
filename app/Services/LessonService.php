<?php

namespace App\Services;

use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;

class LessonService
{
    public function store(array $data, int $userId): Lesson
    {
        $lesson = Lesson::create([
            ...$data,
            'created_by' => $userId,
        ]);

        $lesson->load('module'); // so we can get course_id
        $courseId = $lesson->module?->course_id;

        ActivityLogger::created(
            $userId,
            $courseId,
            'lesson',
            $lesson->id,
            [
                'title' => $lesson->title,
                'content_type' => $lesson->content_type,
                'module_id' => $lesson->module_id,
                'sort_order' => $lesson->sort_order,
            ]
        );

        return $lesson;
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $before = $lesson->only([
            'title', 'content_type', 'sort_order', 'published_at',
            'unlock_after_lesson_id', 'lock_mode',
        ]);

        $lesson->update($data);

        $lesson->load('module');
        $courseId = $lesson->module?->course_id;

        ActivityLogger::updated(
            Auth::id(),
            $courseId,
            'lesson',
            $lesson->id,
            [
                'before' => $before,
                'after' => $lesson->only(array_keys($before)),
            ]
        );

        return $lesson;
    }

    public function delete(Lesson $lesson): void
    {
        $lesson->load('module');
        $courseId = $lesson->module?->course_id;

        $meta = $lesson->only(['title', 'content_type', 'module_id', 'sort_order']);
        $id = $lesson->id;

        $lesson->delete();

        ActivityLogger::deleted(
            Auth::id(),
            $courseId,
            'lesson',
            $id,
            $meta
        );
    }
}

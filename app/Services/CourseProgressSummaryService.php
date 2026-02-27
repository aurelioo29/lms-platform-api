<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseProgressSummary;
use App\Models\Lesson;
use App\Models\LessonProgress;

class CourseProgressSummaryService
{
    public function recalculate(Course $course, int $userId): CourseProgressSummary
    {
        // total lessons in course
        $totalLessons = Lesson::whereHas('module', function ($q) use ($course) {
            $q->where('course_id', $course->id);
        })->count();

        // completed lessons by user
        $completedLessons = LessonProgress::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereIn('lesson_id', function ($q) use ($course) {
                $q->select('lessons.id')
                    ->from('lessons')
                    ->join('course_modules', 'course_modules.id', '=', 'lessons.module_id')
                    ->where('course_modules.course_id', $course->id);
            })
            ->count();

        $percent = $totalLessons > 0
            ? intval(round(($completedLessons / $totalLessons) * 100))
            : 0;

        return CourseProgressSummary::updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id'   => $userId,
            ],
            [
                'total_lessons_count'     => $totalLessons,
                'completed_lessons_count' => $completedLessons,
                'completion_percent'      => $percent,
                'updated_at'              => now(),
            ]
        );
    }
}

<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Support\Facades\Auth;

class LessonProgressService
{
    protected CourseProgressSummaryService $courseSummaryService;

    public function __construct(CourseProgressSummaryService $courseSummaryService)
    {
        $this->courseSummaryService = $courseSummaryService;
    }

    // When student opens lesson → set IN_PROGRESS (50%)
    public function start(Lesson $lesson): LessonProgress
    {
        return LessonProgress::updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'user_id'   => Auth::id(),
            ],
            [
                'status'           => 'in_progress',
                'progress_percent' => 50,
                'started_at'       => now(),
            ]
        );
    }

    // When student clicks "Next Lesson" → mark COMPLETED (100%)
    public function complete(Lesson $lesson): LessonProgress
    {
        $progress = LessonProgress::updateOrCreate(
            [
                'lesson_id' => $lesson->id,
                'user_id'   => Auth::id(),
            ],
            [
                'status'           => 'completed',
                'progress_percent' => 100,
                'completed_at'     => now(),
            ]
        );

        $this->courseSummaryService->recalculate(
            $lesson->module->course,
            Auth::id()
        );

        return $progress;
    }
}

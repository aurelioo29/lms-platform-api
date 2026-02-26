<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseInstructor;
use Illuminate\Support\Facades\Auth;

class CourseInstructorService
{
    // Assign instructor to course
    public function assign(Course $course, int $userId): CourseInstructor
    {
        return CourseInstructor::create([
            'course_id'   => $course->id,
            'user_id'     => $userId,
            'status'      => 'active',
            'assigned_by' => Auth::id(),
            'assigned_at' => now(),
        ]);
    }

    // Update instructor status
    public function updateStatus(CourseInstructor $courseInstructor, string $status): CourseInstructor
    {
        $courseInstructor->update([
            'status' => $status,
        ]);

        return $courseInstructor;
    }

    // Remove instructor from course
    public function remove(CourseInstructor $courseInstructor): void
    {
        $courseInstructor->delete();
    }
}

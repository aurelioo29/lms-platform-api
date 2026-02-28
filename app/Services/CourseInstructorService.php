<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseInstructor;
use Illuminate\Support\Facades\DB;

class CourseInstructorService
{
    public function assign(Course $course, int $instructorUserId, int $actorUserId, ?string $role = 'main'): CourseInstructor
    {
        return DB::transaction(function () use ($course, $instructorUserId, $actorUserId, $role) {
            // create or reactivate/update role
            $existing = CourseInstructor::where('course_id', $course->id)
                ->where('user_id', $instructorUserId)
                ->first();

            $before = $existing ? [
                'role' => $existing->role,
                'assigned_by' => $existing->assigned_by,
            ] : null;

            $instructor = CourseInstructor::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'user_id' => $instructorUserId,
                ],
                [
                    'role' => $role ?: 'main',
                    'assigned_by' => $actorUserId,
                ]
            );

            // Decide event type
            $eventType = $existing ? 'course.instructor_updated' : 'course.instructor_added';

            ActivityLogger::log(
                userId: $actorUserId,
                courseId: (int) $course->id,
                eventType: $eventType,
                refType: CourseInstructor::class,
                refId: (int) $instructor->id,
                meta: [
                    'instructor_user_id' => $instructorUserId,
                    'before' => $before,
                    'after' => [
                        'role' => $instructor->role,
                        'assigned_by' => $instructor->assigned_by,
                    ],
                ]
            );

            return $instructor;
        });
    }

    public function update(CourseInstructor $courseInstructor, array $data, int $actorUserId): CourseInstructor
    {
        return DB::transaction(function () use ($courseInstructor, $data, $actorUserId) {
            $before = [
                'role' => $courseInstructor->role,
                'assigned_by' => $courseInstructor->assigned_by,
            ];

            if (array_key_exists('role', $data) && $data['role']) {
                $courseInstructor->role = $data['role'];
            }

            $courseInstructor->assigned_by = $actorUserId;
            $courseInstructor->save();

            $after = [
                'role' => $courseInstructor->role,
                'assigned_by' => $courseInstructor->assigned_by,
            ];

            ActivityLogger::log(
                userId: $actorUserId,
                courseId: (int) $courseInstructor->course_id,
                eventType: 'course.instructor_updated',
                refType: CourseInstructor::class,
                refId: (int) $courseInstructor->id,
                meta: [
                    'instructor_user_id' => (int) $courseInstructor->user_id,
                    'before' => $before,
                    'after' => $after,
                ]
            );

            return $courseInstructor;
        });
    }

    public function remove(CourseInstructor $courseInstructor, int $actorUserId): void
    {
        DB::transaction(function () use ($courseInstructor, $actorUserId) {
            // log BEFORE delete so refId still valid
            ActivityLogger::log(
                userId: $actorUserId,
                courseId: (int) $courseInstructor->course_id,
                eventType: 'course.instructor_removed',
                refType: CourseInstructor::class,
                refId: (int) $courseInstructor->id,
                meta: [
                    'instructor_user_id' => (int) $courseInstructor->user_id,
                    'role' => $courseInstructor->role,
                ]
            );

            $courseInstructor->delete();
        });
    }
}

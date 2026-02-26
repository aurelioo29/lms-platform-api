<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CourseEnrollmentService
{
    // Enroll authenticated student using enroll key
    public function enrollWithKey(Course $course, string $enrollKey): CourseEnrollment
    {
        $user = Auth::user();

        // course must be published
        if ($course->status !== 'published') {
            throw ValidationException::withMessages([
                'course' => 'Course belum tersedia untuk enrollment.',
            ]);
        }

        // validate enroll key
        if (empty($course->enroll_key_hash) || ! Hash::check($enrollKey, $course->enroll_key_hash)) {
            throw ValidationException::withMessages([
                'enroll_key' => 'Enroll key tidak valid.',
            ]);
        }

        // prevent duplicate enrollment
        $existing = CourseEnrollment::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'active') {
                throw ValidationException::withMessages([
                    'course' => 'Anda sudah terdaftar di course ini.',
                ]);
            }

            // Re-activate enrollment
            $existing->update([
                'status' => 'active',
                'removed_by' => null,
                'removed_at' => null,
                'enrolled_at' => now(),
            ]);

            // ✅ log AFTER update
            ActivityLogger::log(
                userId: (int) $user->id,
                courseId: (int) $course->id,
                eventType: 'course.enrolled_reactivated',
                refType: CourseEnrollment::class,
                refId: (int) $existing->id,
                meta: [
                    'method' => $existing->method,
                    'status' => $existing->status,
                ]
            );

            return $existing;
        }

        // create enrollment (transaction)
        $enrollment = DB::transaction(function () use ($course, $user) {
            return CourseEnrollment::create([
                'course_id' => $course->id,
                'user_id' => $user->id,
                'method' => 'self_key',
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        });

        // ✅ log AFTER create (now $enrollment exists)
        ActivityLogger::log(
            userId: (int) $user->id,
            courseId: (int) $course->id,
            eventType: 'course.enrolled',
            refType: CourseEnrollment::class,
            refId: (int) $enrollment->id,
            meta: [
                'method' => $enrollment->method,
                'status' => $enrollment->status,
            ]
        );

        return $enrollment;
    }

    // Manual enroll by admin or teacher
    public function manualEnroll(Course $course, int $studentId, int $enrolledBy): CourseEnrollment
    {
        // updateOrCreate first so we have an id
        $enrollment = CourseEnrollment::updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $studentId,
            ],
            [
                'method' => 'manual_admin',
                'status' => 'active',
                'enrolled_by' => $enrolledBy,
                'enrolled_at' => now(),
                'removed_at' => null,
                'removed_by' => null,
            ]
        );

        // ✅ log AFTER updateOrCreate
        ActivityLogger::log(
            userId: (int) $enrolledBy,
            courseId: (int) $course->id,
            eventType: 'course.enrolled_manual',
            refType: CourseEnrollment::class,
            refId: (int) $enrollment->id,
            meta: [
                'student_id' => $studentId,
                'method' => $enrollment->method,
                'status' => $enrollment->status,
            ]
        );

        return $enrollment;
    }
}

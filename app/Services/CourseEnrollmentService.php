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

        // couser must be published
        if ($course->status !== "published") {
            throw ValidationException::withMessages(
                [
                    'course' => 'Course belum tersedia  untuk enrollment.',
                ]
            );
        }

        // validate enroll key
        if (empty($course->enroll_key_hash) || !Hash::check($enrollKey, $course->enroll_key_hash)) {
            throw ValidationException::withMessages([
                'enroll_key' => 'Enroll key tidak valid.',
            ]);
        }

        //prevent duplicate enrollment
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

            return $existing;
        }

        //create enrollment
        return DB::transaction(function () use ($course, $user) {
            return CourseEnrollment::create([
                'course_id'   => $course->id,
                'user_id'     => $user->id,
                'method'      => 'self_key',
                'status'      => 'active',
                'enrolled_at' => now(),
            ]);
        });
    }

    // Manual enroll by admin or teacher
    public function manualEnroll(Course $course, int $studentId, int $enrolledBy): CourseEnrollment
    {
        return CourseEnrollment::updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id'   => $studentId,
            ],
            [
                'method'      => 'manual_admin',
                'status'      => 'active',
                'enrolled_by' => $enrolledBy,
                'enrolled_at' => now(),
                'removed_at'  => null,
                'removed_by'  => null,
            ]
        );
    }
}

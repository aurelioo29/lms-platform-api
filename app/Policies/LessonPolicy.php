<?php

namespace App\Policies;

use App\Models\CourseInstructor;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LessonPolicy
{
    private function isStaff(User $user): bool
    {
        $role = $user->role?->value ?? (string) $user->role;

        return in_array($role, ['admin', 'developer', 'teacher'], true);
    }

    private function isAdminOrDev(User $user): bool
    {
        $role = $user->role?->value ?? (string) $user->role;

        return in_array($role, ['admin', 'developer'], true);
    }

    private function isActiveInstructor(User $user, int $courseId): bool
    {
        return CourseInstructor::where('course_id', $courseId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    private function isEnrolled(User $user, int $courseId): bool
    {
        // ✅ SESUAIKAN: ini contoh kalau kamu punya table course_enrollments
        return DB::table('course_enrollments')
            ->where('course_id', $courseId)
            ->where('user_id', $user->id)
            ->exists();

        // Kalau nama tabel kamu beda, ganti di sini.
    }

    public function view(User $user, Lesson $lesson): bool
    {
        $courseId = $lesson->module?->course_id;

        if (! $courseId) {
            return false;
        }

        // Admin/Dev: always allow
        if ($this->isAdminOrDev($user)) {
            return true;
        }

        // Teacher: only if active instructor
        $role = $user->role?->value ?? (string) $user->role;
        if ($role === 'teacher') {
            return $this->isActiveInstructor($user, $courseId);
        }

        // Student: must be enrolled
        return $this->isEnrolled($user, $courseId);
    }

    public function create(User $user, Lesson $lesson): bool
    {
        if ($this->isAdminOrDev($user)) {
            return true;
        }

        $courseId = $lesson->module?->course_id;
        if (! $courseId) {
            return false;
        }

        $role = $user->role?->value ?? (string) $user->role;

        return $role === 'teacher' && $this->isActiveInstructor($user, $courseId);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        if ($this->isAdminOrDev($user)) {
            return true;
        }

        $courseId = $lesson->module?->course_id;
        if (! $courseId) {
            return false;
        }

        $role = $user->role?->value ?? (string) $user->role;

        return $role === 'teacher' && $this->isActiveInstructor($user, $courseId);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}

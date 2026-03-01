<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CourseInstructor;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    private function canManageCourseId(User $user, int $courseId): bool
    {
        if (in_array($user->role, [UserRole::Admin, UserRole::Developer], true)) {
            return true;
        }

        return $user->role === UserRole::Teacher
            && CourseInstructor::where('course_id', $courseId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
    }

    public function create(User $user, Lesson $lesson): bool
    {
        $courseId = CourseModule::where('id', $lesson->module_id)->value('course_id');
        if (! $courseId) {
            return false;
        }

        return $this->canManageCourseId($user, (int) $courseId);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        $lesson->loadMissing('module');
        $courseId = $lesson->module?->course_id;
        if (! $courseId) {
            return false;
        }

        return $this->canManageCourseId($user, (int) $courseId);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->update($user, $lesson);
    }
}

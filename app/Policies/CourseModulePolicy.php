<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\CourseInstructor;
use App\Models\CourseModule;
use App\Models\User;

class CourseModulePolicy
{
    private function canManage(User $user, Course $course): bool
    {
        // Admin + Developer: full access
        if (in_array($user->role, [UserRole::Admin, UserRole::Developer], true)) {
            return true;
        }

        // Teacher: must be active instructor
        return $user->role === UserRole::Teacher
            && CourseInstructor::where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
    }

    public function create(User $user, Course $course): bool
    {
        return $this->canManage($user, $course);
    }

    public function update(User $user, CourseModule $module): bool
    {
        return $this->canManage($user, $module->course);
    }

    public function delete(User $user, CourseModule $module): bool
    {
        return $this->canManage($user, $module->course);
    }
}

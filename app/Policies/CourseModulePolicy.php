<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseInstructor;
use App\Models\CourseModule;
use App\Models\User;

class CourseModulePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    // Teacher can manage modules if they are active instructor of the course
    private function canManage(User $user, Course $course): bool
    {
        // Admins can manage all modules - optional, depending on your app's needs
        // if ($user->role === 'admin') {
        //     return true;
        // }

        return $user->role === 'teacher'
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

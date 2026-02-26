<?php

namespace App\Policies;

use App\Models\CourseInstructor;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    private function canManage(User $user, Lesson $lesson): bool
    {
        // Jika suatu hari admin boleh, tinggal aktifkan
        // if ($user->role === 'admin') {
        //     return true;
        // }

        return $user->role === 'teacher'
            && CourseInstructor::where('course_id', $lesson->module->course_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function create(User $user, Lesson $lesson): bool
    {
        return $this->canManage($user, $lesson);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $this->canManage($user, $lesson);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->canManage($user, $lesson);
    }
}

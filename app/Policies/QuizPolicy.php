<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseInstructor;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    private function canManage(User $user, Course $course): bool
    {
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

    public function update(User $user, Quiz $quiz): bool
    {
        return $this->canManage($user, $quiz->course);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->canManage($user, $quiz->course);
    }
}

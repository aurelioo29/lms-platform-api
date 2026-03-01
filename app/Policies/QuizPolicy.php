<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\CourseInstructor;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Admin/Developer can manage everything.
     * Teacher can manage only if active instructor of the course.
     */
    private function canManageCourse(User $user, Course $course): bool
    {
        $role = $user->role; // casted to UserRole enum

        // ✅ Admin & Developer: full access
        if ($role === UserRole::Admin || $role === UserRole::Developer) {
            return true;
        }

        // ✅ Teacher: only if assigned as active instructor
        if ($role === UserRole::Teacher) {
            return CourseInstructor::query()
                ->where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
        }

        // Student / others: no access
        return false;
    }

    /**
     * Create quiz inside a course
     */
    public function create(User $user, Course $course): bool
    {
        return $this->canManageCourse($user, $course);
    }

    /**
     * Update quiz
     */
    public function update(User $user, Quiz $quiz): bool
    {
        return $this->canManageCourse($user, $quiz->course);
    }

    /**
     * Delete quiz
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->canManageCourse($user, $quiz->course);
    }

    /**
     * Optional: view permissions (if you need later)
     */
    public function view(User $user, Quiz $quiz): bool
    {
        // typically allow enrolled students, but for now:
        return true;
    }
}

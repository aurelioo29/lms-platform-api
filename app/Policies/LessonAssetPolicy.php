<?php

namespace App\Policies;

use App\Models\CourseInstructor;
use App\Models\LessonAsset;
use App\Models\User;

class LessonAssetPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    private function canManage(User $user, LessonAsset $asset): bool
    {
        // Jika admin nanti boleh, tinggal aktifkan
        // if ($user->role === 'admin') return true;

        return $user->role === 'teacher'
            && CourseInstructor::where('course_id', $asset->lesson->module->course_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    public function create(User $user, LessonAsset $asset): bool
    {
        return $this->canManage($user, $asset);
    }

    public function delete(User $user, LessonAsset $asset): bool
    {
        return $this->canManage($user, $asset);
    }
}

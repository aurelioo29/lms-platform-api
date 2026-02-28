<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\CourseInstructor\StoreCourseInstructorRequest;
use App\Http\Requests\Lms\CourseInstructor\UpdateCourseInstructorRequest;
use App\Models\Course;
use App\Models\CourseInstructor;
use App\Services\CourseInstructorService;
use Illuminate\Support\Facades\Auth;

class CourseInstructorController extends Controller
{
    public function __construct(protected CourseInstructorService $service) {}

    private function actorRoleValue(): ?string
    {
        $actor = Auth::user();
        if (! $actor) {
            return null;
        }

        return is_object($actor->role) ? $actor->role->value : (string) $actor->role;
    }

    private function ensureAllowedActor(): ?\Illuminate\Http\JsonResponse
    {
        $role = $this->actorRoleValue();
        if (! $role) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // ✅ IMPORTANT: use 'developer' not 'dev'
        if (! in_array($role, ['admin', 'developer', 'teacher'], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return null;
    }

    public function store(StoreCourseInstructorRequest $request, Course $course)
    {
        if ($resp = $this->ensureAllowedActor()) {
            return $resp;
        }

        $actor = Auth::user();

        $instructor = $this->service->assign(
            course: $course,
            instructorUserId: (int) $request->validated()['user_id'],
            actorUserId: (int) $actor->id,
            role: $request->validated()['role'] ?? 'main'
        );

        return response()->json([
            'message' => 'Instructor assigned.',
            'data' => $instructor->load('instructor'),
        ], 201);
    }

    public function update(UpdateCourseInstructorRequest $request, CourseInstructor $courseInstructor)
    {
        if ($resp = $this->ensureAllowedActor()) {
            return $resp;
        }

        $actor = Auth::user();

        $updated = $this->service->update(
            courseInstructor: $courseInstructor,
            data: $request->validated(),
            actorUserId: (int) $actor->id
        );

        return response()->json([
            'message' => 'Instructor updated.',
            'data' => $updated->load('instructor'),
        ]);
    }

    public function destroy(CourseInstructor $courseInstructor)
    {
        if ($resp = $this->ensureAllowedActor()) {
            return $resp;
        }

        $actor = Auth::user();

        $this->service->deactivate($courseInstructor, (int) $actor->id);

        return response()->json([
            'message' => 'Instructor removed (inactive).',
        ]);
    }
}

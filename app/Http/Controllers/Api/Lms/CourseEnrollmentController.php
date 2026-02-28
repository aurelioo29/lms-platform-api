<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseEnrollmentController extends Controller
{
    public function __construct(protected CourseEnrollmentService $enrollmentService) {}

    // student enroll via key
    public function enrollWithKey(Request $request, Course $course)
    {
        $validated = $request->validate([
            'enroll_key' => ['required', 'string'],
        ]);

        $enrollment = $this->enrollmentService->enrollWithKey($course, $validated['enroll_key']);

        return response()->json([
            'message' => 'Enrolled successfully.',
            'data' => $enrollment,
        ], 201);
    }

    // admin/teacher manual enroll student
    public function manualEnroll(Request $request, Course $course)
    {
        $user = Auth::user();

        $role = is_object($user->role) ? $user->role->value : (string) $user->role;

        if (! in_array($role, ['admin', 'teacher', 'developer'], true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $enrollment = $this->enrollmentService->manualEnroll(
            $course,
            (int) $validated['student_id'],
            (int) $user->id
        );

        return response()->json([
            'message' => 'Student enrolled successfully.',
            'data' => $enrollment,
        ], 201);
    }
}

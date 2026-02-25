<?php

namespace App\Http\Controllers\Api\Lms;

use App\Models\Course;
use App\Services\CourseEnrollmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseEnrollmentController extends Controller
{
    protected  CourseEnrollmentService $enrollmentService;

    public function __construct(CourseEnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }


    // student enroll course using enroll key
    public function enrollWithKey(Request $request, Course $course)
    {
        $request->validate([
            'enroll_key' => ['required', 'string'],
        ]);

        $this->enrollmentService->enrollWithKey(
            $course,
            $request->enroll_key
        );

        return redirect()
            ->back()
            ->with('success', 'Berhasil mendaftar ke course.');
    }


    // manual enroll student by admin
    public function manualEnroll(Request $request, Course $course)
    {
        $request->validate([
            'student_id' => ['required', 'exists:users,id'],
        ]);

        $this->enrollmentService->manualEnroll(
            $course,
            $request->student_id,
            Auth::id()
        );

        return redirect()
            ->back()
            ->with('success', 'Student berhasil didaftarkan ke course.');
    }
}

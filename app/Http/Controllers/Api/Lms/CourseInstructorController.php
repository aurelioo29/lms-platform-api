<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\CourseInstructor\StoreCourseInstructorRequest;
use App\Http\Requests\Lms\CourseInstructor\UpdateCourseInstructorRequest;
use App\Models\Course;
use App\Models\CourseInstructor;
use App\Services\CourseInstructorService;

class CourseInstructorController extends Controller
{
    protected CourseInstructorService $service;

    public function __construct(CourseInstructorService $service)
    {
        $this->service = $service;
    }

    // Assign instructor to course
    public function store(StoreCourseInstructorRequest $request, Course $course)
    {
        $instructor = $this->service->assign(
            $course,
            $request->validated()['user_id']
        );

        return response()->json($instructor, 201);
    }


    // Update instructor status
    public function update(UpdateCourseInstructorRequest $request, CourseInstructor $courseInstructor)
    {
        return response()->json(
            $this->service->updateStatus(
                $courseInstructor,
                $request->validated()['status']
            )
        );
    }

    // Remove instructor from course
    public function destroy(CourseInstructor $courseInstructor)
    {
        $this->service->remove($courseInstructor);

        return response()->json([
            'message' => 'Instructor removed successfully',
        ]);
    }
}

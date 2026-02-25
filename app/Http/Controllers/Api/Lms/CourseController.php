<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\Course\StoreCourseRequest;
use App\Http\Requests\Lms\Course\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    // list publish courses (student)
    public function index()
    {
        return response()->json(
            Course::where('status', 'published')
                ->latest()
                ->get()
        );
    }

    // create course (admin)
    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->create($request->validated());

        return response()->json($course, 201);
    }

    // update course
    public function update(UpdateCourseRequest $request, Course $course)
    {

        return response()->json($this->courseService->update($course, $request->validated()));
    }

    // publish course
    public function publish(Request $request, Course $course)
    {
        return response()->json(
            $this->courseService->publish($course, $request->validated()['enroll_key'] ?? null)
        );
    }

    // archive course
    public function archive(Course $course)
    {
        return response()->json(
            $this->courseService->archive($course)
        );
    }
}

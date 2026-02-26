<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\Course\PublishCourseRequest;
use App\Http\Requests\Lms\Course\StoreCourseRequest;
use App\Http\Requests\Lms\Course\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(protected CourseService $courseService) {}

    // students: list published courses
    public function index()
    {
        return response()->json(
            Course::query()
                ->where('status', 'published')
                ->latest()
                ->get()
        );
    }

    public function adminIndex(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');

        $courses = Course::query()
            ->when($status, fn ($qq) => $qq->where('status', $status))
            ->when($q, fn ($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->latest()
            ->get();

        return response()->json($courses);
    }

    // admin: create
    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->create($request->validated());

        return response()->json($course, 201);
    }

    // admin: update
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course = $this->courseService->update($course, $request->validated());

        return response()->json($course);
    }

    // admin: publish
    public function publish(PublishCourseRequest $request, Course $course)
    {
        $course = $this->courseService->publish(
            $course,
            $request->validated()['enroll_key'] ?? null
        );

        return response()->json($course);
    }

    // admin: archive
    public function archive(Course $course)
    {
        return response()->json($this->courseService->archive($course));
    }
}

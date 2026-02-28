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

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $userId = (int) $request->user()->id;

        $courses = \App\Models\Course::query()
            ->where('status', 'published')
            ->when($q !== '', fn ($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->with([
                'courseInstructors.instructor:id,name', // ✅ penting
            ])
            ->withExists([
                // hasilnya akan jadi boolean: is_enrolled (0/1)
                'enrollments as is_enrolled' => fn ($en) => $en
                    ->where('user_id', $userId)
                    ->where('status', 'active'),
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json($courses);
    }

    public function adminIndex(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');

        $courses = Course::query()
            ->when($status, fn ($qq) => $qq->where('status', $status))
            ->when($q, fn ($qq) => $qq->where('title', 'like', "%{$q}%"))
            ->with([
                'courseInstructors.instructor:id,name',
            ])
            ->latest()
            ->get();

        return response()->json($courses);
    }

    public function store(StoreCourseRequest $request)
    {
        $result = $this->courseService->create($request->validated());

        return response()->json([
            'data' => $result['course'],
            'enroll_key' => $result['enroll_key'],
        ], 201);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $result = $this->courseService->update($course, $request->validated());

        return response()->json([
            'data' => $result['course'],
            'enroll_key' => $result['enroll_key'],
        ]);
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

    public function showBySlug(string $slug)
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with(['courseInstructors.instructor:id,name']) // ✅ add this
            ->first();

        if (! $course) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json($course);
    }
}

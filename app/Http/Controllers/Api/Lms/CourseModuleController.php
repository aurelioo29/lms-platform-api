<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\CourseModule\StoreCourseModuleRequest;
use App\Http\Requests\Lms\CourseModule\UpdateCourseModuleRequest;
use App\Models\Course;
use App\Models\CourseModule;
use App\Services\CourseModuleService;
use Illuminate\Http\Request;

class CourseModuleController extends Controller
{
    protected CourseModuleService $service;

    public function __construct(CourseModuleService $service)
    {
        $this->service = $service;
    }

    // list modules in course
    public function index(Course $course)
    {
        return response()->json(
            $this->service->listByCourse($course)
        );
    }

    // create module
    public function store(StoreCourseModuleRequest $request, Course $course)
    {
        return response()->json(
            $this->service->create($course, $request->validated()),
            201
        );
    }

    // update module
    public function update(UpdateCourseModuleRequest $request, CourseModule $module)
    {

        return response()->json(
            $this->service->update($module, $request->validated())
        );
    }

    // delete module
    public function destroy(CourseModule $module)
    {
        $this->authorize('delete', $module);

        $this->service->delete($module);

        return response()->json(['message' => 'Module deleted']);
    }
}

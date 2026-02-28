<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Support\Facades\Auth;

class CourseModuleService
{
    public function listByCourse(Course $course)
    {
        return CourseModule::where('course_id', $course->id)
            ->orderBy('sort_order')
            ->get();
    }

    public function create(Course $course, array $data): CourseModule
    {
        return CourseModule::create([
            ...$data,
            'course_id'  => $course->id,
            'created_by' => Auth::id(),
            'sort_order' => $data['sort_order'] ?? 1,
        ]);
    }

    public function update(CourseModule $module, array $data): CourseModule
    {
        $module->update($data);

        return $module->refresh();
    }

    public function delete(CourseModule $module): void
    {
        $module->delete();
    }
}

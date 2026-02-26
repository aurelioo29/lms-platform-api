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
            'course_id'  => $course->id,
            'title'      => $data['title'],
            'sort_order' => $data['sort_order'] ?? 1,
            'created_by' => Auth::id(),
        ]);
    }

    public function update(CourseModule $module, array $data): CourseModule
    {
        $module->update([
            'title'      => $data['title'] ?? $module->title,
            'sort_order' => $data['sort_order'] ?? $module->sort_order,
        ]);

        return $module;
    }

    public function delete(CourseModule $module): void
    {
        $module->delete();
    }
}

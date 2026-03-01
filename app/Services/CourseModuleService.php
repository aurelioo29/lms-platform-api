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
            ->with(['lessons' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    public function create(Course $course, array $data): CourseModule
    {
        $module = CourseModule::create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'sort_order' => $data['sort_order'] ?? 1,
            'created_by' => Auth::id(),
        ]);

        ActivityLogger::created(
            Auth::id(),
            $course->id,
            'course_module',
            $module->id,
            [
                'title' => $module->title,
                'sort_order' => $module->sort_order,
            ]
        );

        return $module;
    }

    public function update(CourseModule $module, array $data): CourseModule
    {
        $before = $module->only(['title', 'sort_order']);

        $module->update([
            'title' => $data['title'] ?? $module->title,
            'sort_order' => $data['sort_order'] ?? $module->sort_order,
        ]);

        ActivityLogger::updated(
            Auth::id(),
            $module->course_id,
            'course_module',
            $module->id,
            [
                'before' => $before,
                'after' => $module->only(['title', 'sort_order']),
            ]
        );

        return $module;
    }

    public function delete(CourseModule $module): void
    {
        $meta = $module->only(['title', 'sort_order']);
        $courseId = $module->course_id;
        $id = $module->id;

        $module->delete();

        ActivityLogger::deleted(
            Auth::id(),
            $courseId,
            'course_module',
            $id,
            $meta
        );
    }
}

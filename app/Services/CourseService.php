<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CourseService
{
    public function create(array $data): Course
    {
        return Course::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);
    }

    public function update(Course $course, array $data): Course
    {
        $course->update([
            'title' => $data['title'] ?? $course->title,
            'description' => $data['description'] ?? $course->description,
        ]);

        return $course;
    }

    public function publish(Course $course, ?string $enrollKey = null): Course
    {
        if ($enrollKey) {
            $course->enroll_key_hash = Hash::make($enrollKey);
        }

        $course->status = 'published';
        $course->published_at = now();
        $course->save();

        return $course;
    }

    public function archive(Course $course): Course
    {
        $course->update([
            'status' => 'archived',
        ]);

        return $course;
    }
}

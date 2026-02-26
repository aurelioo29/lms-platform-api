<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CourseService
{
    public function create(array $data): Course
    {
        return DB::transaction(function () use ($data) {
            $course = Course::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            ActivityLogger::log(
                userId: (int) Auth::id(),
                courseId: (int) $course->id,
                eventType: 'course.created',
                refType: Course::class,
                refId: (int) $course->id,
                meta: [
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'status' => $course->status,
                ]
            );

            return $course;
        });
    }

    public function update(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data) {
            $before = [
                'title' => $course->title,
                'description' => $course->description,
            ];

            $course->update([
                'title' => $data['title'] ?? $course->title,
                'description' => $data['description'] ?? $course->description,
            ]);

            $after = [
                'title' => $course->title,
                'description' => $course->description,
            ];

            ActivityLogger::log(
                userId: (int) Auth::id(),
                courseId: (int) $course->id,
                eventType: 'course.updated',
                refType: Course::class,
                refId: (int) $course->id,
                meta: [
                    'before' => $before,
                    'after' => $after,
                ]
            );

            return $course;
        });
    }

    public function publish(Course $course, ?string $enrollKey = null): Course
    {
        return DB::transaction(function () use ($course, $enrollKey) {
            $before = [
                'status' => $course->status,
                'published_at' => $course->published_at?->toISOString(),
                'has_enroll_key' => ! empty($course->enroll_key_hash),
            ];

            if ($enrollKey) {
                $course->enroll_key_hash = Hash::make($enrollKey);
            }

            $course->status = 'published';
            $course->published_at = now();
            $course->save();

            $after = [
                'status' => $course->status,
                'published_at' => $course->published_at?->toISOString(),
                'has_enroll_key' => ! empty($course->enroll_key_hash),
            ];

            ActivityLogger::log(
                userId: (int) Auth::id(),
                courseId: (int) $course->id,
                eventType: 'course.published',
                refType: Course::class,
                refId: (int) $course->id,
                meta: [
                    'before' => $before,
                    'after' => $after,
                    'enroll_key_set' => (bool) $enrollKey,
                ]
            );

            return $course;
        });
    }

    public function archive(Course $course): Course
    {
        return DB::transaction(function () use ($course) {
            $beforeStatus = $course->status;

            $course->update([
                'status' => 'archived',
            ]);

            ActivityLogger::log(
                userId: (int) Auth::id(),
                courseId: (int) $course->id,
                eventType: 'course.archived',
                refType: Course::class,
                refId: (int) $course->id,
                meta: [
                    'before_status' => $beforeStatus,
                    'after_status' => $course->status,
                ]
            );

            return $course;
        });
    }
}

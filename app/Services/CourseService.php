<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CourseService
{
    public function create(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $userId = (int) Auth::id();

            $status = $data['status'] ?? 'draft';
            $title = $data['title'];
            $slug = Str::slug($title);

            // Decide enroll key
            $plainKey = null;
            $wantsGenerate = (bool) ($data['auto_generate_key'] ?? false);

            if (! empty($data['enroll_key'])) {
                $plainKey = trim($data['enroll_key']);
            } elseif ($wantsGenerate) {
                $plainKey = $this->generateKey(8);
            }

            $course = Course::create([
                'title' => $title,
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'status' => $status,
                'created_by' => $userId,
                'published_at' => $status === 'published' ? now() : null,

                // store all forms
                'enroll_key_plain' => $plainKey, // ✅ PLAIN TEXT (INSECURE)
                'enroll_key_hash' => $plainKey ? Hash::make($plainKey) : null,
                'enroll_key_enc' => $plainKey ? Crypt::encryptString($plainKey) : null,
            ]);

            ActivityLogger::log(
                userId: $userId,
                courseId: (int) $course->id,
                eventType: 'course.created',
                refType: Course::class,
                refId: (int) $course->id,
                meta: [
                    'status' => $course->status,
                    'slug' => $course->slug,
                    'has_key' => (bool) $plainKey,
                ]
            );

            // IMPORTANT:
            // course_enrollments SHOULD NOT be created here unless you *explicitly* want auto-enroll.
            // It represents who enrolled, not the course key.

            return [
                'course' => $course,
                'enroll_key' => $plainKey, // return once so UI can show/copy
            ];
        });
    }

    public function update(Course $course, array $data): array
    {
        return DB::transaction(function () use ($course, $data) {
            $userId = (int) Auth::id();

            $before = [
                'title' => $course->title,
                'description' => $course->description,
                'status' => $course->status,
                'has_key' => ! empty($course->enroll_key_hash),
            ];

            if (array_key_exists('title', $data)) {
                $course->title = $data['title'];
                $course->slug = Str::slug($data['title']);
            }

            if (array_key_exists('description', $data)) {
                $course->description = $data['description'];
            }

            if (array_key_exists('status', $data)) {
                $course->status = $data['status'];
                $course->published_at = $course->status === 'published'
                    ? ($course->published_at ?? now())
                    : null;
            }

            // key update (optional)
            $plainKey = null;
            $wantsGenerate = (bool) ($data['auto_generate_key'] ?? false);

            if (! empty($data['enroll_key'])) {
                $plainKey = trim($data['enroll_key']);
            } elseif ($wantsGenerate) {
                $plainKey = $this->generateKey(8);
            }

            if ($plainKey !== null) {
                $course->enroll_key_plain = $plainKey;
                $course->enroll_key_hash = Hash::make($plainKey);
                $course->enroll_key_enc = Crypt::encryptString($plainKey);
            }

            $course->save();

            $after = [
                'title' => $course->title,
                'description' => $course->description,
                'status' => $course->status,
                'has_key' => ! empty($course->enroll_key_hash),
            ];

            ActivityLogger::log(
                userId: $userId,
                courseId: (int) $course->id,
                eventType: 'course.updated',
                refType: Course::class,
                refId: (int) $course->id,
                meta: [
                    'before' => $before,
                    'after' => $after,
                    'key_regenerated' => (bool) $plainKey,
                ]
            );

            return [
                'course' => $course,
                'enroll_key' => $plainKey, // if regenerated, return once
            ];
        });
    }

    private function generateKey(int $len = 8): string
    {
        // avoid confusing chars 0,O,1,l
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $out;
    }

    public function archive(Course $course): Course
    {
        return DB::transaction(function () use ($course) {
            $userId = (int) Auth::id();

            $before = [
                'status' => $course->status,
                'published_at' => $course->published_at,
            ];

            $course->status = 'archived';
            $course->published_at = null; // optional: archived berarti tidak published
            $course->save();

            $after = [
                'status' => $course->status,
                'published_at' => $course->published_at,
            ];

            ActivityLogger::log(
                userId: $userId,
                courseId: (int) $course->id,
                eventType: 'course.archived',
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
}

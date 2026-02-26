<?php

namespace App\Services;

use App\Models\Lesson;

class LessonService
{
    public function store(array $data, int $userId): Lesson
    {
        return Lesson::create([
            ...$data,
            'created_by' => $userId,
        ]);
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);
        return $lesson;
    }

    public function delete(Lesson $lesson): void
    {
        $lesson->delete();
    }
}

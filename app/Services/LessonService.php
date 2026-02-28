<?php

namespace App\Services;

use App\Models\Lesson;

class LessonService
{
    public function store(array $data, int $userId): Lesson
    {
        $data['created_by'] = $userId;

        return Lesson::create($data);
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);

        return $lesson->refresh();
    }

    public function delete(Lesson $lesson): void
    {
        $lesson->delete();
    }
}

<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class QuizService
{
    public function create(Course $course, array $data): Quiz
    {
        $data['course_id'] = $course->id;
        $data['created_by'] = Auth::id();

        return Quiz::create($data);
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        $quiz->update($data);

        return $quiz;
    }

    public function delete(Quiz $quiz): void
    {
        $quiz->delete();
    }
}

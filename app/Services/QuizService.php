<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseInstructor;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class QuizService
{
    private function ensureInstructor(Course $course): void
    {
        $isInstructor = CourseInstructor::query()
            ->where('course_id', $course->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        if (! $isInstructor) {
            abort(403, 'You are not instructor of this course');
        }
    }

    public function create(Course $course, array $data): Quiz
    {
        $this->ensureInstructor($course);

        $data['course_id'] = $course->id;
        $data['created_by'] = Auth::id();

        return Quiz::create($data);
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        $this->ensureInstructor($quiz->course);

        $quiz->update($data);

        return $quiz;
    }

    public function delete(Quiz $quiz): void
    {
        $this->ensureInstructor($quiz->course);
        $quiz->delete();
    }
}

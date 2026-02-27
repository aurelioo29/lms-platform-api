<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\Quiz\StoreQuizRequest;
use App\Http\Requests\Lms\Quiz\UpdateQuizRequest;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private QuizService $service) {}

    public function store(StoreQuizRequest $request, Course $course)
    {
        $this->authorize('create', [Quiz::class, $course]);

        $quiz = $this->service->create($course, $request->validated());

        return response()->json($quiz, 201);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $quiz = $this->service->update($quiz, $request->validated());

        return response()->json($quiz);
    }

    public function destroy(Quiz $quiz)
    {
        $this->authorize('delete', $quiz);

        $this->service->delete($quiz);

        return response()->json(['message' => 'Quiz deleted']);
    }
}

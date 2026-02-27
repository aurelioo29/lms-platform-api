<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\QuizQuestion\StoreQuizQuestionRequest;
use App\Http\Requests\Lms\QuizQuestion\UpdateQuizQuestionRequest;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\QuizQuestionService;
use Illuminate\Http\Request;

class QuizQuestionController extends Controller
{
    protected QuizQuestionService $service;

    public function __construct(QuizQuestionService $service)
    {
        $this->service = $service;
    }

    public function index(Quiz $quiz)
    {
        return response()->json(
            $this->service->listByQuiz($quiz)
        );
    }

    public function store(StoreQuizQuestionRequest $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        return response()->json(
            $this->service->create($quiz, $request->validated()),
            201
        );
    }

    public function update(UpdateQuizQuestionRequest $request, QuizQuestion $quizQuestion)
    {
        $this->authorize('update', $quizQuestion->quiz);

        return response()->json(
            $this->service->update($quizQuestion, $request->validated())
        );
    }

    public function destroy(QuizQuestion $quizQuestion)
    {
        $this->authorize('update', $quizQuestion->quiz);

        $this->service->delete($quizQuestion);

        return response()->json(['message' => 'Question deleted']);
    }
}

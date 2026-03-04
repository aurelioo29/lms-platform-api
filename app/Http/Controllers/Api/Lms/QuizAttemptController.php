<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\QuizAttempt\StartQuizAttemptRequest;
use App\Http\Requests\Lms\QuizAttempt\SubmitQuizAttemptRequest;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizAttemptService;
use Illuminate\Http\Request;

class QuizAttemptController extends Controller
{
    public function __construct(protected QuizAttemptService $service) {}

    public function index(Quiz $quiz)
    {
        return response()->json(
            $this->service->myAttempts($quiz)
        );
    }

    public function storePublic(Request $request, Quiz $quiz)
    {
        // optional: authorize view/attempt quiz
        // $this->authorize('attempt', $quiz);

        $attempt = $this->service->storePublic($quiz, $request->all());

        return response()->json($attempt, 201);
    }

    public function start(StartQuizAttemptRequest $request, Quiz $quiz)
    {
        return response()->json(
            $this->service->start($quiz),
            201
        );
    }

    public function submit(SubmitQuizAttemptRequest $request, QuizAttempt $attempt)
    {
        return response()->json(
            $this->service->submit($attempt)
        );
    }
}

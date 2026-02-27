<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\QuizAnswer\GradeQuizAnswerRequest;
use App\Http\Requests\Lms\QuizAnswer\SaveQuizAnswerRequest;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Services\QuizAnswerService;
use Illuminate\Http\Request;

class QuizAnswerController extends Controller
{
    public function __construct(protected QuizAnswerService $service) {}


    public function save(SaveQuizAnswerRequest $request, QuizAttempt $attempt, QuizQuestion $question)
    {
        return response()->json(
            $this->service->save(
                $attempt,
                $question,
                $request->validated()
            )
        );
    }


    public function grade(GradeQuizAnswerRequest $request, QuizAnswer $answer)
    {
        return response()->json(
            $this->service->gradeManually(
                $answer,
                $request->validated()['points']
            )
        );
    }
}

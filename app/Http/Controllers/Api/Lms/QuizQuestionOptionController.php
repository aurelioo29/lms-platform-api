<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\QuestionOption\StoreQuizQuestionOptionRequest;
use App\Http\Requests\Lms\QuestionOption\UpdateQuizQuestionOptionRequest;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Services\QuizQuestionOptionService;
use Illuminate\Http\Request;

class QuizQuestionOptionController extends Controller
{
    protected QuizQuestionOptionService $service;

    public function __construct(QuizQuestionOptionService $service)
    {
        $this->service = $service;
    }

    public function index(QuizQuestion $question)
    {
        return response()->json(
            $this->service->listByQuestion($question)
        );
    }

    public function store(StoreQuizQuestionOptionRequest $request, QuizQuestion $question)
    {
        $this->authorize('update', $question->quiz);

        return response()->json(
            $this->service->create($question, $request->validated()),
            201
        );
    }

    public function update(UpdateQuizQuestionOptionRequest $request, QuizQuestionOption $option)
    {
        $this->authorize('update', $option->question->quiz);

        return response()->json(
            $this->service->update($option, $request->validated())
        );
    }

    public function destroy(QuizQuestionOption $option)
    {
        $this->authorize('update', $option->question->quiz);

        $this->service->delete($option);

        return response()->json(['message' => 'Option deleted']);
    }
}

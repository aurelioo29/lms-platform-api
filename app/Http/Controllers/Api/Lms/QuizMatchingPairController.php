<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\QuizMatchingPair\StoreQuizMatchingPairRequest;
use App\Http\Requests\Lms\QuizMatchingPair\UpdateQuizMatchingPairRequest;
use App\Models\QuizMatchingPair;
use App\Models\QuizQuestion;
use App\Services\QuizMatchingPairService;
use Illuminate\Http\Request;

class QuizMatchingPairController extends Controller
{
    public function __construct(protected QuizMatchingPairService $service) {}

    public function index(QuizQuestion $question)
    {
        return response()->json(
            $this->service->list($question)
        );
    }

    public function store(StoreQuizMatchingPairRequest $request, QuizQuestion $question)
    {
        return response()->json(
            $this->service->create($question, $request->validated()),
            201
        );
    }

    public function update(UpdateQuizMatchingPairRequest $request, QuizMatchingPair $pair)
    {
        return response()->json(
            $this->service->update($pair, $request->validated())
        );
    }

    public function destroy(QuizMatchingPair $pair)
    {
        $this->authorize('update', $pair->question);

        $this->service->delete($pair);

        return response()->json([
            'message' => 'Matching pair deleted'
        ]);
    }
}

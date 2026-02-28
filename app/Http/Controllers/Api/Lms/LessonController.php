<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\Lesson\StoreLessonRequest;
use App\Http\Requests\Lms\Lesson\UpdateLessonRequest;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function __construct(protected LessonService $service) {}

    public function store(StoreLessonRequest $request)
    {
        $lesson = $this->service->store(
            $request->validated(),
            Auth::id()
        );

        return response()->json($lesson, 201);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        $lesson = $this->service->update(
            $lesson,
            $request->validated()
        );

        return response()->json($lesson);
    }

    public function destroy(Lesson $lesson)
    {
        $this->service->delete($lesson);

        return response()->json([
            'message' => 'Lesson deleted'
        ]);
    }
}

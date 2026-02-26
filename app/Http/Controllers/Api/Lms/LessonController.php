<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\Lesson\StoreLessonRequest;
use App\Http\Requests\Lms\Lesson\UpdateLessonRequest;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function __construct(protected LessonService $service) {}

    public function store(StoreLessonRequest $request)
    {
        $lesson = new Lesson([
            'module_id' => $request->module_id,
        ]);

        $this->authorize('create', $lesson);

        return response()->json(
            $this->service->store($request->validated(), auth()->id()),
            201
        );
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        $this->authorize('update', $lesson);

        return response()->json(
            $this->service->update($lesson, $request->validated())
        );
    }

    public function destroy(Lesson $lesson)
    {
        $this->authorize('delete', $lesson);

        $this->service->delete($lesson);

        return response()->json(['message' => 'Lesson deleted']);
    }
}

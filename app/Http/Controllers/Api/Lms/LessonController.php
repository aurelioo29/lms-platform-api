<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\Lesson\StoreLessonRequest;
use App\Http\Requests\Lms\Lesson\UpdateLessonRequest;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Support\Facades\Storage;

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

    public function show(Lesson $lesson)
    {
        $this->authorize('view', $lesson);

        $lesson->load([
            'module:id,course_id,title',
            'assets:id,lesson_id,type,title,url,file_path,mime_type,size_bytes',
        ]);

        $asset = $lesson->assets->sortByDesc('id')->first();

        // bikin URL file kalau pakai storage public
        $assetUrl = null;
        if ($asset) {
            $assetUrl = $asset->url ?: ($asset->file_path ? Storage::disk('public')->url($asset->file_path) : null);
        }

        return response()->json([
            'id' => $lesson->id,
            'module_id' => $lesson->module_id,
            'title' => $lesson->title,
            'content_type' => $lesson->content_type,
            'content_json' => $lesson->content_json,

            // ✅ khusus resource
            'resource_type' => $asset?->type,
            'resource_url' => $assetUrl,
            'resource_mime' => $asset?->mime_type,

            'module' => $lesson->module ? [
                'id' => $lesson->module->id,
                'title' => $lesson->module->title,
                'course_id' => $lesson->module->course_id,
            ] : null,
        ]);
    }
}

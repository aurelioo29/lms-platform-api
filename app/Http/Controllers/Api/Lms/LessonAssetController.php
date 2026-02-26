<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\LessonAsset\StoreLessonAssetRequest;
use App\Models\LessonAsset;
use App\Services\LessonAssetService;
use Illuminate\Http\Request;

class LessonAssetController extends Controller
{
    public function __construct(protected LessonAssetService $service) {}

    public function store(StoreLessonAssetRequest $request)
    {
        $asset = new LessonAsset([
            'lesson_id' => $request->lesson_id,
        ]);

        $this->authorize('create', $asset);

        return response()->json(
            $this->service->store($request->validated(), auth()->id()),
            201
        );
    }

    public function destroy(LessonAsset $lessonAsset)
    {
        $this->authorize('delete', $lessonAsset);

        $this->service->delete($lessonAsset);

        return response()->json(['message' => 'Asset deleted']);
    }
}

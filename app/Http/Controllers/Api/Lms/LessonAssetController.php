<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\LessonAsset\StoreLessonAssetRequest;
use App\Models\LessonAsset;
use App\Services\LessonAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonAssetController extends Controller
{
    public function __construct(protected LessonAssetService $service) {}

    public function store(StoreLessonAssetRequest $request)
    {
        $asset = $this->service->store(
            $request->validated(),
            Auth::id()
        );

        return response()->json($asset, 201);
    }

    public function destroy(LessonAsset $lessonAsset)
    {
        $this->service->delete($lessonAsset);

        return response()->json([
            'message' => 'Asset deleted'
        ]);
    }
}

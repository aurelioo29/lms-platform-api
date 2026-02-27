<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\LessonProgressService;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    protected LessonProgressService $service;

    public function __construct(LessonProgressService $service)
    {
        $this->service = $service;
    }

    // Called when lesson page is opened
    public function start(Lesson $lesson)
    {
        $progress = $this->service->start($lesson);

        return response()->json([
            'message' => 'Lesson started',
            'data'    => $progress,
        ]);
    }

    // Called when user clicks "Next Lesson"
    public function complete(Lesson $lesson)
    {
        $progress = $this->service->complete($lesson);

        return response()->json([
            'message' => 'Lesson completed',
            'data'    => $progress,
        ]);
    }
}

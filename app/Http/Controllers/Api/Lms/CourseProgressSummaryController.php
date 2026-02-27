<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseProgressSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseProgressSummaryController extends Controller
{
    public function show(Course $course)
    {
        $summary = CourseProgressSummary::where('course_id', $course->id)
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'data' => $summary,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseDiscussion;
use Illuminate\Http\Request;

class AdminDiscussionController extends Controller
{
    // GET /api/admin/discussions?page=&per_page=&q=&status=&course_id=
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status');     // open|locked|hidden|null
        $courseId = $request->get('course_id');  // optional

        $query = CourseDiscussion::query()
            ->with([
                'course:id,title,slug',
                'user:id,name,email',
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('id', $q); // quick search by id if numeric-like
                });
            })
            ->when($status, fn ($qq) => $qq->where('status', $status))
            ->when($courseId, fn ($qq) => $qq->where('course_id', $courseId))
            ->orderByDesc('id');

        return response()->json([
            'data' => $query->paginate($perPage),
        ]);
    }

    // PATCH /api/admin/discussions/{discussion}/toggle-lock
    public function toggleLock(CourseDiscussion $discussion, Request $request)
    {
        // lock <-> open (don't touch hidden)
        if ($discussion->status === 'hidden') {
            return response()->json([
                'message' => 'Hidden discussion cannot be toggled here.',
            ], 422);
        }

        $discussion->status = $discussion->status === 'locked' ? 'open' : 'locked';
        $discussion->save();

        return response()->json([
            'data' => $discussion->fresh()->load('course:id,title,slug', 'user:id,name,email'),
        ]);
    }
}

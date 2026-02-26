<?php

namespace App\Http\Controllers\Api\Discussion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discussion\StoreDiscussionRequest;
use App\Models\Course;
use App\Models\CourseDiscussion;
use Illuminate\Http\Request;

class CourseDiscussionController extends Controller
{
    public function index(Request $request, Course $course)
    {
        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);
        $q = trim((string) $request->get('q', ''));

        $query = CourseDiscussion::query()
            ->where('course_id', $course->id)
            ->with(['user:id,name', 'reactions'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%");
            })
            ->orderBy('id', 'asc');

        $user = $request->user();
        $isStaff = $user && in_array($user->role?->value, ['teacher', 'admin', 'developer'], true);

        if (! $isStaff) {
            $query->where('status', '!=', 'hidden');
        }

        return response()->json([
            'data' => $query->paginate($perPage),
        ]);
    }

    // POST /api/courses/{course}/discussions
    public function store(StoreDiscussionRequest $request, Course $course)
    {
        $user = $request->user();

        $isPrivileged = in_array($user->role?->value, ['admin', 'teacher', 'developer'], true);

        if (! $isPrivileged) {
            $cooldownMinutes = (int) config('lms.discussion_cooldown_minutes', 10);

            $last = CourseDiscussion::query()
                ->where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->first();

            if ($last && $last->created_at->diffInSeconds(now()) < ($cooldownMinutes * 60)) {
                $remainSec = ($cooldownMinutes * 60) - $last->created_at->diffInSeconds(now());

                return response()->json([
                    'message' => 'Cooldown aktif. Tunggu sebentar sebelum membuat diskusi baru.',
                    'retry_after_seconds' => $remainSec,
                ], 429);
            }
        }

        $data = $request->validated();

        $discussion = CourseDiscussion::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'title' => $data['title'],
            'body_json' => $data['body_json'],
            'status' => 'open',
        ]);

        return response()->json($discussion, 201);
    }

    // GET /api/discussions/{discussion}
    public function show(Request $request, CourseDiscussion $discussion)
    {
        $user = $request->user();
        $isStaff = $user && in_array($user->role?->value, ['teacher', 'admin', 'developer'], true);

        if ($discussion->status === 'hidden' && ! $isStaff) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $discussion->load([
            'user:id,name',
            'reactions',
            'comments.user:id,name',
            'comments.reactions',
        ]);

        return response()->json(['data' => $discussion]);
    }

    // PATCH /api/discussions/{discussion}  (edit title/body) (author or staff)
    public function update(Request $request, CourseDiscussion $discussion)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isStaff = in_array($user->role?->value, ['teacher', 'admin', 'developer'], true);
        $isOwner = $discussion->user_id === $user->id;

        if (! $isOwner && ! $isStaff) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:180'],
            'body_json' => ['sometimes', 'array'],
            'status' => ['sometimes', 'in:open,locked,hidden'], // only staff should change status
        ]);

        if (isset($data['status']) && ! $isStaff) {
            unset($data['status']);
        }

        $discussion->fill($data)->save();

        return response()->json(['data' => $discussion->fresh()->load('user:id,name')]);
    }

    // DELETE /api/discussions/{discussion}
    public function destroy(Request $request, CourseDiscussion $discussion)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isStaff = in_array($user->role?->value, ['teacher', 'admin', 'developer'], true);
        $isOwner = $discussion->user_id === $user->id;

        if (! $isOwner && ! $isStaff) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $discussion->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function setStatus(Request $request, CourseDiscussion $discussion)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isStaff = in_array($user->role?->value, ['teacher', 'admin', 'developer'], true);
        if (! $isStaff) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:open,locked,hidden'],
        ]);

        $discussion->status = $data['status'];
        $discussion->save();

        return response()->json(['data' => $discussion->fresh()->load('user:id,name')]);
    }
}

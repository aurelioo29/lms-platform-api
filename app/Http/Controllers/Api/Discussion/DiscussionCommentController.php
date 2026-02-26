<?php

namespace App\Http\Controllers\Api\Discussion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discussion\StoreCommentRequest;
use App\Models\CourseDiscussion;
use App\Models\DiscussionComment;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DiscussionCommentController extends Controller
{
    // POST /api/discussions/{discussion}/comments
    public function store(StoreCommentRequest $request, CourseDiscussion $discussion)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($discussion->status !== 'open') {
            return response()->json([
                'message' => 'Diskusi sedang ditutup. Tidak bisa komentar.',
            ], 403);
        }

        $data = $request->validated();

        $comment = DiscussionComment::create([
            'discussion_id' => $discussion->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body_json' => $data['body_json'],
        ]);

        ActivityLogger::log(
            userId: $user->id,
            courseId: $discussion->course_id,
            eventType: 'discussion_comment_created',
            refType: 'discussion_comment',
            refId: $comment->id,
            meta: ['discussion_id' => $discussion->id, 'ip' => $request->ip()]
        );

        return response()->json([
            'data' => $comment->load('user:id,name'),
        ], 201);
    }

    // DELETE /api/comments/{comment}
    public function destroy(Request $request, DiscussionComment $comment)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isStaff = in_array($user->role?->value, ['teacher', 'admin', 'developer'], true);
        $isOwner = $comment->user_id === $user->id;

        if (! $isOwner && ! $isStaff) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function show(CourseDiscussion $discussion, Request $request)
    {
        $user = $request->user();

        $isPrivileged = $user->hasRole('admin') || $user->hasRole('teacher') || $user->hasRole('dev');

        if ($discussion->status === 'hidden' && ! $isPrivileged) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($discussion);
    }
}

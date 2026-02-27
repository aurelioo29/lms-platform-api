<?php

namespace App\Http\Controllers\Api\Discussion;

use App\Http\Controllers\Controller;
use App\Models\CourseDiscussion;
use App\Models\DiscussionComment;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'reaction' => ['required', Rule::in(['like', 'love', 'laugh', 'insight', 'confused'])],
            'reactable_type' => ['required', 'string'],
            'reactable_id' => ['required', 'integer'],
        ]);

        // Allow short aliases OR full class names
        $typeMap = [
            'discussion' => CourseDiscussion::class,
            'comment' => DiscussionComment::class,
            CourseDiscussion::class => CourseDiscussion::class,
            DiscussionComment::class => DiscussionComment::class,
        ];

        $reactableType = $typeMap[$data['reactable_type']] ?? null;
        if (! $reactableType) {
            return response()->json([
                'message' => 'Invalid reactable_type',
            ], 422);
        }

        $userId = $request->user()->id;

        // Toggle: if exists -> delete, else -> create
        $existing = Reaction::query()
            ->where('user_id', $userId)
            ->where('reaction', $data['reaction'])
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $data['reactable_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Reaction::create([
                'user_id' => $userId,
                'reaction' => $data['reaction'],
                'reactable_type' => $reactableType,
                'reactable_id' => $data['reactable_id'],
            ]);
            $liked = true;
        }

        // Return counts so UI can update instantly
        $likesCount = Reaction::query()
            ->where('reaction', 'like')
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $data['reactable_id'])
            ->count();

        return response()->json([
            'data' => [
                'liked' => $liked,
                'likes_count' => $likesCount,
            ],
        ]);
    }
}

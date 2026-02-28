<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class MyCourseController extends Controller
{
    // GET /api/my/courses?per_page=20&q=
    public function index(Request $request)
    {
        $user = $request->user();

        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);
        $q = trim((string) $request->get('q', ''));

        $query = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with([
                'course:id,title,slug,status,published_at,created_at',
            ])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->whereHas('course', function ($c) use ($q) {
                    $c->where('title', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('enrolled_at')
            ->select([
                'id',
                'course_id',
                'user_id',
                'method',
                'status',
                'enrolled_at',
                'last_accessed_at',
                'created_at',
                'updated_at',
            ]);

        return response()->json([
            'data' => $query->paginate($perPage),
        ]);
    }
}

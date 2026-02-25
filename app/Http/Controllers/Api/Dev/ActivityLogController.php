<?php

namespace App\Http\Controllers\Api\Dev;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'id');
        $dir = strtolower($request->get('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['id', 'created_at'], true)) {
            $sort = 'id';
        }

        $q = ActivityLog::query()
            ->with(['user:id,name']) // ✅ load user name
            ->orderBy($sort, $dir);

        if ($request->filled('course_id')) {
            $q->where('course_id', $request->integer('course_id'));
        }

        if ($request->filled('user_id')) {
            $q->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('event_type')) {
            $q->where('event_type', (string) $request->string('event_type'));
        }

        if ($request->filled('date_from')) {
            $q->where('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $q->where('created_at', '<=', $request->date('date_to')->endOfDay());
        }

        $perPage = min(max((int) $request->get('per_page', 50), 1), 200);

        $paginator = $q->paginate($perPage);

        // ✅ transform the SAME paginator (no re-paginate)
        $paginator->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user_name' => $log->user?->name, // ✅ now available
                'course_id' => $log->course_id,
                'event_type' => $log->event_type,
                'meta_json' => $log->meta_json,
                'created_at' => $log->created_at?->toISOString(), // keep UTC
                'created_at_wib' => $log->created_at
                    ? $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
                    : null,
            ];
        });

        return response()->json([
            'data' => $paginator,
        ]);
    }

    public function show($id)
    {
        $log = ActivityLog::with(['user:id,name'])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user_name' => $log->user?->name,
                'course_id' => $log->course_id,
                'event_type' => $log->event_type,
                'meta_json' => $log->meta_json,
                'created_at' => $log->created_at?->toISOString(),
                'created_at_wib' => $log->created_at
                    ? $log->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s')
                    : null,
            ],
        ]);
    }
}

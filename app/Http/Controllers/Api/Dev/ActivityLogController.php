<?php

namespace App\Http\Controllers\Api\Dev;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * GET /api/dev/activity-logs
     * Filters: course_id, user_id, event_type, ref_type, ref_id, date_from, date_to
     * Pagination: per_page
     */
    public function index(Request $request)
    {
        $q = ActivityLog::query()->latest('created_at');

        if ($request->filled('course_id')) {
            $q->where('course_id', $request->integer('course_id'));
        }

        if ($request->filled('user_id')) {
            $q->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('event_type')) {
            $q->where('event_type', $request->string('event_type'));
        }

        if ($request->filled('ref_type')) {
            $q->where('ref_type', $request->string('ref_type'));
        }

        if ($request->filled('ref_id')) {
            $q->where('ref_id', $request->integer('ref_id'));
        }

        if ($request->filled('date_from')) {
            $q->where('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            // include end-of-day
            $q->where('created_at', '<=', $request->date('date_to')->endOfDay());
        }

        $perPage = min(max((int) $request->get('per_page', 50), 1), 200);

        return response()->json([
            'data' => $q->paginate($perPage),
        ]);
    }

    public function show($id)
    {
        $log = ActivityLog::findOrFail($id);

        return response()->json([
            'data' => $log,
        ]);
    }
}

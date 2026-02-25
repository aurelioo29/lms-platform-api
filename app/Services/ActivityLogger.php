<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        int $userId,
        ?int $courseId,
        string $eventType,
        ?string $refType = null,
        ?int $refId = null,
        array $meta = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id'    => $userId,
            'course_id'  => $courseId,
            'event_type' => $eventType,
            'ref_type'   => $refType,
            'ref_id'     => $refId,
            'meta_json'  => empty($meta) ? null : $meta,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public const CREATE = 'create';

    public const UPDATE = 'update';

    public const DELETE = 'delete';

    public static function log(
        int $userId,
        ?int $courseId,
        string $eventType,
        ?string $refType = null,
        ?int $refId = null,
        array $meta = []
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'event_type' => $eventType,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'meta_json' => empty($meta) ? null : $meta,
            'created_at' => now(),
        ]);
    }

    public static function created(int $userId, ?int $courseId, string $refType, int $refId, array $meta = []): ActivityLog
    {
        return self::log($userId, $courseId, self::CREATE, $refType, $refId, $meta);
    }

    public static function updated(int $userId, ?int $courseId, string $refType, int $refId, array $meta = []): ActivityLog
    {
        return self::log($userId, $courseId, self::UPDATE, $refType, $refId, $meta);
    }

    public static function deleted(int $userId, ?int $courseId, string $refType, int $refId, array $meta = []): ActivityLog
    {
        return self::log($userId, $courseId, self::DELETE, $refType, $refId, $meta);
    }
}

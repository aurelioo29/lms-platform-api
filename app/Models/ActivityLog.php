<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false; // created_at only (useCurrent)

    protected $table = 'activity_logs';

    protected $fillable = [
        'course_id',
        'user_id',
        'event_type',
        'ref_type',
        'ref_id',
        'meta_json',
        'created_at',
    ];

    protected $casts = [
        'meta_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

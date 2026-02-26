<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscussionComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'discussion_id',
        'user_id',
        'parent_id',
        'body_json',
    ];

    protected $casts = [
        'body_json' => 'array',
    ];

    public function discussion()
    {
        return $this->belongsTo(CourseDiscussion::class, 'discussion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(DiscussionComment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DiscussionComment::class, 'parent_id');
    }

    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }
}

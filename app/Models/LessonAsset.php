<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAsset extends Model
{
    use HasFactory;

    public const TYPE_PDF = 'pdf';
    public const TYPE_VIDEO_EMBED = 'video_embed';
    public const TYPE_VIDEO_UPLOAD = 'video_upload';
    public const TYPE_IMAGE = 'image';
    public const TYPE_FILE = 'file';

    protected $fillable = [
        'lesson_id',
        'type',
        'title',
        'url',
        'file_path',
        'mime_type',
        'size_bytes',
        'uploaded_by',
    ];


    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

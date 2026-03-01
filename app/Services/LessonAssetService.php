<?php

namespace App\Services;

use App\Models\LessonAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LessonAssetService
{
    public function store(array $data, int $userId): LessonAsset
    {
        if (empty($data['url']) && empty($data['file'])) {
            throw ValidationException::withMessages([
                'asset' => 'URL or file must be provided',
            ]);
        }

        $filePath = null;
        $mimeType = null;
        $size = null;

        if (! empty($data['file']) && $data['file'] instanceof UploadedFile) {
            $filePath = $data['file']->store('lesson-assets', 'public');
            $mimeType = $data['file']->getClientMimeType();
            $size = $data['file']->getSize();
        }

        $asset = LessonAsset::create([
            'lesson_id' => $data['lesson_id'],
            'type' => $data['type'],
            'title' => $data['title'] ?? null,
            'url' => $data['url'] ?? null,
            'file_path' => $filePath,
            'mime_type' => $mimeType,
            'size_bytes' => $size,
            'uploaded_by' => $userId,
        ]);

        $asset->load('lesson.module');
        $courseId = $asset->lesson?->module?->course_id;

        ActivityLogger::created(
            $userId,
            $courseId,
            'lesson_asset',
            $asset->id,
            [
                'lesson_id' => $asset->lesson_id,
                'type' => $asset->type,
                'title' => $asset->title,
                'url' => $asset->url,
                'file_path' => $asset->file_path,
                'mime_type' => $asset->mime_type,
                'size_bytes' => $asset->size_bytes,
            ]
        );

        return $asset;
    }

    public function delete(LessonAsset $asset): void
    {
        $asset->load('lesson.module');
        $courseId = $asset->lesson?->module?->course_id;

        $meta = $asset->only([
            'lesson_id', 'type', 'title', 'url', 'file_path', 'mime_type', 'size_bytes',
        ]);
        $id = $asset->id;

        if ($asset->file_path) {
            Storage::disk('public')->delete($asset->file_path);
        }

        $asset->delete();

        ActivityLogger::deleted(
            Auth::id(),
            $courseId,
            'lesson_asset',
            $id,
            $meta
        );
    }
}

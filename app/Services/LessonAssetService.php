<?php

namespace App\Services;

use App\Models\LessonAsset;
use Illuminate\Http\UploadedFile;
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

        if (!empty($data['file']) && $data['file'] instanceof UploadedFile) {
            $filePath = $data['file']->store('lesson-assets', 'public');
            $mimeType = $data['file']->getClientMimeType();
            $size = $data['file']->getSize();
        }

        return LessonAsset::create([
            'lesson_id' => $data['lesson_id'],
            'type' => $data['type'],
            'title' => $data['title'] ?? null,
            'url' => $data['url'] ?? null,
            'file_path' => $filePath,
            'mime_type' => $mimeType,
            'size_bytes' => $size,
            'uploaded_by' => $userId,
        ]);
    }

    public function delete(LessonAsset $asset): void
    {
        if ($asset->file_path) {
            Storage::disk('public')->delete($asset->file_path);
        }

        $asset->delete();
    }
}

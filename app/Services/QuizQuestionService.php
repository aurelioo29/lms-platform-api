<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizQuestionService
{
    public function listByQuiz(Quiz $quiz)
    {
        return $quiz->questions()
            ->orderBy('sort_order')
            ->get();
    }

    public function create(Quiz $quiz, array $data, ?UploadedFile $mediaFile = null): QuizQuestion
    {
        return DB::transaction(function () use ($quiz, $data, $mediaFile) {
            $payload = $this->basePayload($data);

            // normalize default
            $payload += $this->normalizeMediaInput($data);

            /** @var QuizQuestion $question */
            $question = $quiz->questions()->create($payload);

            if (($data['media_type'] ?? 'none') === 'upload' && $mediaFile) {
                $this->storeUploadToQuestion($question, $mediaFile);
            }

            return $question->fresh();
        });
    }

    public function update(QuizQuestion $question, array $data, ?UploadedFile $mediaFile = null): QuizQuestion
    {
        return DB::transaction(function () use ($question, $data, $mediaFile) {

            // clear media requested
            if (! empty($data['clear_media'])) {
                $this->clearQuestionMedia($question);
            }

            // update base fields
            $payload = $this->basePayload($data, partial: true);

            // media fields (only if sent)
            if (array_key_exists('media_type', $data) || array_key_exists('media_url', $data)) {
                $payload += $this->normalizeMediaInput($data);

                // if switching away from upload, delete old file
                if (($payload['media_type'] ?? null) !== 'upload') {
                    $this->deleteOldUploadIfAny($question);
                }
            }

            $question->update($payload);

            // upload replace / set
            if (($data['media_type'] ?? null) === 'upload' && $mediaFile) {
                $this->storeUploadToQuestion($question, $mediaFile);
            }

            // youtube switch: ensure media_path cleared
            if (($data['media_type'] ?? null) === 'youtube') {
                $question->update([
                    'media_path' => null,
                ]);
            }

            // none switch: ensure both cleared
            if (($data['media_type'] ?? null) === 'none') {
                $this->clearQuestionMedia($question);
            }

            return $question->fresh();
        });
    }

    public function delete(QuizQuestion $question): void
    {
        DB::transaction(function () use ($question) {
            $this->deleteOldUploadIfAny($question);
            $question->delete();
        });
    }

    private function basePayload(array $data, bool $partial = false): array
    {
        $map = [
            'question_type' => 'question_type',
            'prompt' => 'prompt',
            'prompt_json' => 'prompt_json',
            'points' => 'points',
            'sort_order' => 'sort_order',
        ];

        $out = [];
        foreach ($map as $key => $col) {
            if ($partial) {
                if (array_key_exists($key, $data)) {
                    $out[$col] = $data[$key];
                }
            } else {
                $out[$col] = $data[$key] ?? ($key === 'points' ? 1 : null);
            }
        }

        return $out;
    }

    private function normalizeMediaInput(array $data): array
    {
        $type = $data['media_type'] ?? 'none';

        $out = [
            'media_type' => $type,
            'require_watch' => (bool) ($data['require_watch'] ?? false),
            'min_watch_seconds' => $data['min_watch_seconds'] ?? null,
        ];

        if ($type === 'youtube') {
            $out['media_url'] = $data['media_url'] ?? null;
            $out['media_path'] = null;
        } elseif ($type === 'upload') {
            $out['media_url'] = null;
            // media_path will be filled after storing file
        } else { // none
            $out['media_url'] = null;
            $out['media_path'] = null;
            $out['media_meta'] = null;
            $out['require_watch'] = false;
            $out['min_watch_seconds'] = null;
        }

        return $out;
    }

    private function storeUploadToQuestion(QuizQuestion $question, UploadedFile $file): void
    {
        $this->deleteOldUploadIfAny($question);

        $path = $file->store("quiz/questions/{$question->id}", 'public');

        $meta = array_merge((array) ($question->media_meta ?? []), [
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            // duration_seconds: kalau mau akurat, perlu ffprobe (nanti bisa kita tambah)
        ]);

        $question->update([
            'media_type' => 'upload',
            'media_path' => $path,
            'media_url' => null,
            'media_meta' => $meta,
        ]);
    }

    private function clearQuestionMedia(QuizQuestion $question): void
    {
        $this->deleteOldUploadIfAny($question);

        $question->update([
            'media_type' => 'none',
            'media_url' => null,
            'media_path' => null,
            'media_meta' => null,
            'require_watch' => false,
            'min_watch_seconds' => null,
        ]);
    }

    private function deleteOldUploadIfAny(QuizQuestion $question): void
    {
        if ($question->media_type === 'upload' && $question->media_path) {
            Storage::disk('public')->delete($question->media_path);
        }
    }
}

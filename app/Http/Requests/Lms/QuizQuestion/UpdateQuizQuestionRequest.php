<?php

namespace App\Http\Requests\Lms\QuizQuestion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_type' => ['sometimes', Rule::in([
                'mcq_single',
                'mcq_multi',
                'essay',
                'rating',
                'matching',
                'true_false',
            ])],

            'prompt' => ['sometimes', 'string'],
            'prompt_json' => ['sometimes', 'nullable', 'array'],
            'points' => ['sometimes', 'integer', 'min:0'],
            'sort_order' => ['sometimes', 'integer', 'min:1'],

            'media_type' => ['sometimes', Rule::in(['none', 'upload', 'youtube'])],
            'media_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'media_file' => ['sometimes', 'nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:512000'],

            'require_watch' => ['sometimes', 'boolean'],
            'min_watch_seconds' => ['sometimes', 'nullable', 'integer', 'min:1'],

            // optional helper: admin bisa request hapus video
            'clear_media' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $type = $this->input('media_type', null);

            // kalau clear_media=true, kita izinkan tanpa mikirin type/url/file
            if ($this->boolean('clear_media')) {
                return;
            }

            if ($type === 'youtube') {
                if (! $this->filled('media_url')) {
                    $v->errors()->add('media_url', 'media_url is required when media_type is youtube.');
                }
                if ($this->hasFile('media_file')) {
                    $v->errors()->add('media_file', 'media_file must be empty when media_type is youtube.');
                }
            }

            if ($type === 'upload') {
                if ($this->filled('media_url')) {
                    $v->errors()->add('media_url', 'media_url must be empty when media_type is upload.');
                }
            }

            if ($type === 'none') {
                if ($this->filled('media_url') || $this->hasFile('media_file')) {
                    $v->errors()->add('media_type', 'Set media_type to youtube/upload if you provide media.');
                }
            }
        });
    }
}

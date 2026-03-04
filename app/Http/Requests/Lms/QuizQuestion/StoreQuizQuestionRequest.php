<?php

namespace App\Http\Requests\Lms\QuizQuestion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_type' => ['required', Rule::in([
                'mcq_single',
                'mcq_multi',
                'essay',
                'rating',
                'matching',
                'true_false',
            ])],

            'prompt' => ['required', 'string'],
            'prompt_json' => ['nullable', 'array'],
            'points' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:1'],

            // ✅ media fields (optional)
            'media_type' => ['nullable', Rule::in(['none', 'upload', 'youtube'])],
            'media_url' => ['nullable', 'string', 'max:2048'],
            'media_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:512000'], // 500MB

            'require_watch' => ['nullable', 'boolean'],
            'min_watch_seconds' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $type = $this->input('media_type', 'none');

            if ($type === 'youtube') {
                if (! $this->filled('media_url')) {
                    $v->errors()->add('media_url', 'media_url is required when media_type is youtube.');
                }
                if ($this->hasFile('media_file')) {
                    $v->errors()->add('media_file', 'media_file must be empty when media_type is youtube.');
                }
            }

            if ($type === 'upload') {
                if (! $this->hasFile('media_file')) {
                    $v->errors()->add('media_file', 'media_file is required when media_type is upload.');
                }
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

<?php

namespace App\Http\Requests\Lms\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content_json' => ['nullable', 'array'],
            'content_type' => ['sometimes', 'in:lesson,video,quiz'],
            'sort_order' => ['sometimes', 'integer', 'min:1'],
            'published_at' => ['nullable', 'date'],
            'unlock_after_lesson_id' => ['nullable', 'exists:lessons,id'],
            'lock_mode' => ['sometimes', 'in:open,complete'],
        ];
    }
}

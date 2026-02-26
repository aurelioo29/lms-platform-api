<?php

namespace App\Http\Requests\Lms\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
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
            'type' => ['sometimes', 'in:mcq,essay,feedback,matching,puzzle'],
            'time_limit_seconds' => ['nullable', 'integer', 'min:1'],
            'attempt_limit' => ['nullable', 'integer', 'min:1'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}

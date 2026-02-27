<?php

namespace App\Http\Requests\Lms\QuestionOption;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionOptionRequest extends FormRequest
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
            'label'      => ['sometimes', 'string'],
            'is_correct' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}

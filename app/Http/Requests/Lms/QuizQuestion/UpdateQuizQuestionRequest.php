<?php

namespace App\Http\Requests\Lms\QuizQuestion;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizQuestionRequest extends FormRequest
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
            'question_type' => [
                'required',
                'in:mcq_single,mcq_multi,essay,rating,matching,true_false'
            ],
            'prompt' => ['required', 'string'],
            'prompt_json' => ['nullable', 'array'],
            'points' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

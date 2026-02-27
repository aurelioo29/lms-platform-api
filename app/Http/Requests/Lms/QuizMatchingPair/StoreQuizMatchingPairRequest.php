<?php

namespace App\Http\Requests\Lms\QuizMatchingPair;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizMatchingPairRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $question = $this->route('question');

        return $this->user()->can('update', $question);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'left_text'  => ['required', 'string'],
            'right_text' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

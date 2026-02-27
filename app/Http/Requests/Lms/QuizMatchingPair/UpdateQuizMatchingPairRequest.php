<?php

namespace App\Http\Requests\Lms\QuizMatchingPair;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizMatchingPairRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $pair = $this->route('pair');

        return $this->user()->can('update', $pair->question);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'left_text'  => ['sometimes', 'string'],
            'right_text' => ['sometimes', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}

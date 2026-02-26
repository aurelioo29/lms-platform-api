<?php

namespace App\Http\Requests\Lms\QuizAnswer;

use Illuminate\Foundation\Http\FormRequest;

class SaveQuizAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $attempt = $this->route('attempt');

        return $attempt->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answer_json' => ['required', 'array'],
        ];
    }
}

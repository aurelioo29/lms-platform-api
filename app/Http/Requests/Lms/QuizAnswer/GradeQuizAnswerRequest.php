<?php

namespace App\Http\Requests\Lms\QuizAnswer;

use Illuminate\Foundation\Http\FormRequest;

class GradeQuizAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $answer = $this->route('answer');

        return $this->user()->can('grade', $answer->question);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'points' => ['required', 'integer', 'min:0'],
        ];
    }
}

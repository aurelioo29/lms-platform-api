<?php

namespace App\Http\Requests\Lms\CourseInstructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseInstructorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'in:main,assistant'],
        ];
    }
}

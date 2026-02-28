<?php

namespace App\Http\Requests\Lms\CourseInstructor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseInstructorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,inactive'],
            'role' => ['nullable', 'in:main,assistant'],
        ];
    }
}

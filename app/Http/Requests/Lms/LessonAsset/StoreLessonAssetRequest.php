<?php

namespace App\Http\Requests\Lms\LessonAsset;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonAssetRequest extends FormRequest
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
            'lesson_id' => ['required', 'exists:lessons,id'],
            'type' => ['required', 'in:pdf,video_embed,video_upload,image,file'],
            'title' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'url'],
            'file' => ['nullable', 'file', 'max:51200'], // 50MB
        ];
    }
}

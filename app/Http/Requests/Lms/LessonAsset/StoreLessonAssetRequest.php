<?php

namespace App\Http\Requests\Lms\LessonAsset;

use App\Models\LessonAsset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            'type' => [
                'required',
                Rule::in([
                    LessonAsset::TYPE_PDF,
                    LessonAsset::TYPE_VIDEO_EMBED,
                    LessonAsset::TYPE_VIDEO_UPLOAD,
                    LessonAsset::TYPE_IMAGE,
                    LessonAsset::TYPE_FILE,
                ]),
            ],

            'title' => ['nullable', 'string', 'max:255'],

            // URL hanya wajib untuk embed
            'url' => [
                'nullable',
                'url',
                'required_if:type,' . LessonAsset::TYPE_VIDEO_EMBED,
            ],

            // File wajib untuk upload types
            'file' => [
                'nullable',
                'file',
                'max:51200',
                'required_if:type,' . LessonAsset::TYPE_VIDEO_UPLOAD,
                'required_if:type,' . LessonAsset::TYPE_PDF,
                'required_if:type,' . LessonAsset::TYPE_IMAGE,
                'required_if:type,' . LessonAsset::TYPE_FILE,
            ],
        ];
    }
}

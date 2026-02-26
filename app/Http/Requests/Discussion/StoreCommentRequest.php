<?php

namespace App\Http\Requests\Discussion;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body_json' => ['required', 'array'],
            'parent_id' => ['nullable', 'integer', 'exists:discussion_comments,id'],
        ];
    }
}

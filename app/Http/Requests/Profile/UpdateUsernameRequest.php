<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUsernameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:60'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if (!$user) return;

            $last = $user->username_changed_at;

            // Kalau belum pernah ganti, aman
            if (!$last) return;

            // 60 hari sekali
            $nextAllowed = $last->copy()->addDays(60);

            if (now()->lt($nextAllowed)) {
                $daysLeft = now()->diffInDays($nextAllowed) ?: 1;
                $validator->errors()->add(
                    'name',
                    "You can change your display name once every 60 days. Try again in {$daysLeft} day(s)."
                );
            }
        });
    }
}

<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:24', 'confirmed', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token reset tidak ditemukan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 24 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.regex' => 'Password harus mengandung setidaknya satu huruf kecil, satu huruf besar, dan satu angka.',
        ];
    }
}

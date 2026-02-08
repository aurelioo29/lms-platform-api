<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function forgot(ForgotPasswordRequest $request)
    {
        // Laravel akan kirim reset link kalau email valid/terdaftar
        $status = Password::sendResetLink($request->only('email'));

        // Security: jangan bocorin email itu ada atau nggak
        return response()->json([
            'message' => 'Jika email terdaftar, link reset password akan dikirim.',
            'status' => $status,
        ], 200);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status), // bisa "This password reset token is invalid."
                'status' => $status,
            ], 422);
        }

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login.',
        ], 200);
    }
}

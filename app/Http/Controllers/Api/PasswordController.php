<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function forgot(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink($request->only('email'));

        $user = User::where('email', $request->email)->first();
        if ($user) {
            ActivityLogger::log(
                userId: $user->id,
                courseId: null,
                eventType: 'forgot_password_request',
                refType: 'user',
                refId: $user->id,
                meta: [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            );
        }

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

        if ($status === Password::PASSWORD_RESET) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                ActivityLogger::log(
                    userId: $user->id,
                    courseId: null,
                    eventType: 'password_reset',
                    refType: 'user',
                    refId: $user->id,
                    meta: [
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]
                );
            }
        }

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login.',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\ActivityLogger;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => UserRole::Student->value,
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'register',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email untuk verifikasi akun.',
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email belum diverifikasi.',
                'code' => 'EMAIL_NOT_VERIFIED',
            ], 409);
        }

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'login',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->value,
                'google_id' => $user->google_id,
                'avatar' => $user->avatar,
                'username_changed_at' => $user->username_changed_at,
            ] : null,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        ActivityLogger::log(
            userId: $user?->id ?? 0,
            courseId: null,
            eventType: 'logout',
            refType: 'user',
            refId: $user?->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }
}

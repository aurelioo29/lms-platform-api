<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Link verifikasi tidak valid.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Link verifikasi sudah tidak berlaku (email sudah terverifikasi).'
            ], 410);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'email_verified',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        // FE bisa bikin page "/verify-success"
        return response()->json(['message' => 'Verifikasi email berhasil. Silakan login.'], 200);
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // jangan bocorin email exist / tidak (security)
            return response()->json([
                'message' => 'Jika email terdaftar, link verifikasi akan dikirim.',
            ], 200);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah terverifikasi. Silakan login.',
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        ActivityLogger::log(
            userId: $user->id,
            courseId: null,
            eventType: 'email_verification_resend',
            refType: 'user',
            refId: $user->id,
            meta: [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'message' => 'Link verifikasi sudah dikirim ulang. Cek inbox/spam.',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        // stateless() wajib kalau ini API (tanpa session/cookie)
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        // cari user berdasarkan email (paling aman buat linking)
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(32)), // random karena login via google
            ]);
        }

        // simpan google_id + avatar untuk referensi
        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        // === OPSI A: kalau kamu pakai Laravel Sanctum (API token) ===
        // Pastikan Sanctum sudah terpasang, lalu:
        $token = $user->createToken('google-auth')->plainTextToken;

        // Kamu bisa redirect ke frontend sambil bawa token
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        return redirect()->away($frontendUrl . '/auth/callback?token=' . urlencode($token));

        // === OPSI B: kalau mau JSON langsung ===
        // return response()->json([
        //     'token' => $token,
        //     'user' => $user,
        // ]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

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
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $googleUser->getName() ?? 'Google User',
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(Str::random(32)),
                // kalau kamu butuh role default:
                'role' => 'student',
                // penting kalau sistem kamu minta verified:
                'email_verified_at' => now(),
            ]);
        }

        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            // kalau user lama belum verified, beresin:
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        // ✅ INI KUNCINYA:
        Auth::guard('web')->login($user);
        request()->session()->regenerate();

        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

        // sekarang frontend tinggal lempar ke dashboard, TANPA token
        return redirect()->away($frontendUrl . '/dashboard');
    }
}

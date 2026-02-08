<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /**
         * 1) Email Verification
         * Backend bikin signed URL asli (buat validasi signature),
         * tapi user diarahkan ke Next.js dengan query verify_url
         */
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $backendUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            return rtrim(config('app.frontend_url'), '/') .
                '/verify-email?verify_url=' . urlencode($backendUrl);
        });

        /**
         * 2) Reset Password
         * Link reset langsung menuju UI Next.js
         */
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontend = rtrim(config('app.frontend_url'), '/');
            $email = urlencode($notifiable->getEmailForPasswordReset());

            return "{$frontend}/reset-password?token={$token}&email={$email}";
        });
    }
}

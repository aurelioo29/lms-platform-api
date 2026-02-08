<?php

namespace App\Notifications;

use App\Mail\VerifyAccountMail;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $backendUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $frontend = rtrim(config('app.frontend_url'), '/');
        $actionUrl = $frontend . '/verify-email?verify_url=' . urlencode($backendUrl);

        return (new VerifyAccountMail(
            actionUrl: $actionUrl,
            name: $notifiable->name ?? 'User',
            appName: config('app.name'),
        ))->to($notifiable->email);
    }
}

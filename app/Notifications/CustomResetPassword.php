<?php

namespace App\Notifications;

use App\Mail\ResetPasswordMail;
use Illuminate\Notifications\Notification;

class CustomResetPassword extends Notification
{
    public function __construct(public string $token) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontend = rtrim(config('app.frontend_url'), '/');
        $email = urlencode($notifiable->getEmailForPasswordReset());

        $actionUrl = "{$frontend}/reset-password?token={$this->token}&email={$email}";

        return (new ResetPasswordMail($actionUrl))->to($notifiable->email);
    }
}

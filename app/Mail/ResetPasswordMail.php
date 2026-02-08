<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ResetPasswordMail extends Mailable
{
    public function __construct(public string $actionUrl) {}

    public function build()
    {
        return $this->subject('Reset Password')
            ->view('emails.auth.reset-password', [
                'actionUrl' => $this->actionUrl,
            ]);
    }
}

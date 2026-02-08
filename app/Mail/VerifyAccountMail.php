<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $actionUrl,
        public string $name,
        public string $appName,
    ) {}

    public function build()
    {
        return $this->subject("Verify your email - {$this->appName}")
            ->view('emails.auth.verify')
            ->with([
                'actionUrl' => $this->actionUrl,
                'name' => $this->name,
                'appName' => $this->appName,
            ]);
    }
}

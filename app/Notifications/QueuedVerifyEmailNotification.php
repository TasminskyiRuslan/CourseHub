<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('high');
    }

    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Please, confirm your email address.')
            ->action('Confirm email', $url);
    }
}

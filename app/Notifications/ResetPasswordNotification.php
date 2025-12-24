<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $reset_token;
    public function __construct(string $reset_token)
    {
        $this->reset_token = $reset_token;
        $this->onQueue('high');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('They took away the sheet to reset the password.')
            ->action('Reset Password', $this->reset_token);
    }
}

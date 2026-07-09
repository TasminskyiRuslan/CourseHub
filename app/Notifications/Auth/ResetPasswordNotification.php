<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     * @return void
     */
    public function __construct(public string $token)
    {
        $this->onQueue('high');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $resetUrl = url('/password/reset?token=' . $this->token);

        return (new MailMessage)
            ->subject('Reset Password')
            ->markdown('emails.auth.reset', [
                'url' => $resetUrl,
                'token' => $this->token,
                'user' => $notifiable,
            ]);
    }
}

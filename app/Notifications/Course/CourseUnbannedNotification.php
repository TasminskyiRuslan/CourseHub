<?php

declare(strict_types=1);

namespace App\Notifications\Course;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseUnbannedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Course $course)
    {
        $this->onQueue('high');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Course Unbanned')
            ->markdown('emails.course.unban', [
                'user' => $notifiable,
                'course' => $this->course,
                'url' => route('teacher.courses.show', $this->course->slug),
            ]);
    }
}

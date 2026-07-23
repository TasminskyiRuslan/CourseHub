<?php

namespace App\Listeners;

use App\Actions\Course\EnrollUserAction;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Log; // ◄ Додаємо логгер
use Laravel\Cashier\Events\WebhookReceived;

readonly class StripeCheckoutCompletedListener
{
    public function __construct(
        private EnrollUserAction $enrollUserAction
    ) {}

    public function handle(WebhookReceived $event): void
    {
        // 1. Логуємо факт отримання події
        Log::info('Stripe Webhook Received:', ['type' => $event->payload['type'] ?? 'unknown']);

        if ($event->payload['type'] !== 'checkout.session.completed') {
            return;
        }

        $session = $event->payload['data']['object'];

        $userId = $session['metadata']['user_id'] ?? null;
        $courseId = $session['metadata']['course_id'] ?? null;

        Log::info('Stripe Checkout Session Data:', ['user_id' => $userId, 'course_id' => $courseId]);

        if ($userId && $courseId) {
            $user = User::query()->find($userId);
            $course = Course::query()->find($courseId);

            if ($user && $course) {
                $this->enrollUserAction->handle($user, $course);
                Log::info("User {$userId} successfully enrolled in course {$courseId}");
            } else {
                Log::error("User or Course not found in database. User: {$userId}, Course: {$courseId}");
            }
        } else {
            Log::warning('Stripe Session missing metadata for user_id or course_id');
        }
    }
}

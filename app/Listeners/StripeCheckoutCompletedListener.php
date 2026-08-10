<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\Course\EnrollUserAction;
use App\Models\Course;
use App\Models\User;
// ◄ Додаємо логгер
use Laravel\Cashier\Events\WebhookReceived;

readonly class StripeCheckoutCompletedListener
{
    public function __construct(
        private EnrollUserAction $enrollUserAction
    ) {}

    /**
     * Handle the Stripe checkout completed event and enroll the user.
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->payload['type'] !== 'checkout.session.completed') {
            return;
        }

        $session = $event->payload['data']['object'];

        $userId = $session['metadata']['user_id'] ?? null;
        $courseId = $session['metadata']['course_id'] ?? null;

        if ($userId && $courseId) {
            $user = User::query()->find($userId);
            $course = Course::query()->find($courseId);

            if ($user && $course) {
                $this->enrollUserAction->handle($user, $course);
            }
        }
    }
}

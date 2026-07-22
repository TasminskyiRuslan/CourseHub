<?php

namespace App\Listeners;

use App\Actions\Course\EnrollUserAction;
use App\Models\Course;
use App\Models\User;
use Laravel\Cashier\Events\WebhookReceived;

class StripeCheckoutCompletedListener
{
    /**
     * @param EnrollUserAction $enrollUserAction
     */
    public function __construct(
        private readonly EnrollUserAction $enrollUserAction
    )
    {
    }

    /**
     * Handle the webhook event from Stripe.
     *
     * @param WebhookReceived $event
     * @return void
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

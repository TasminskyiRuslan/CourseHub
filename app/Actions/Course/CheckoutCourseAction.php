<?php

declare(strict_types=1);

namespace App\Actions\Course;

use App\Data\Course\Results\CheckoutResultData;
use App\Enums\CheckoutStatus;
use App\Models\Course;
use App\Models\User;

readonly class CheckoutCourseAction
{
    public function __construct(
        private EnrollUserAction $enrollUserAction
    ) {}

    /**
     * Check out the specified course for the specified user.
     */
    public function handle(User $user, Course $course): CheckoutResultData
    {
        if ($user->isEnrolledIn($course)) {
            return new CheckoutResultData(
                status: CheckoutStatus::ENROLLED,
                courseId: $course->id,
                checkoutUrl: null
            );
        }

        if ($course->isFree()) {
            $this->enrollUserAction->handle($user, $course);

            return new CheckoutResultData(
                status: CheckoutStatus::ENROLLED,
                courseId: $course->id,
                checkoutUrl: null
            );
        }

        $baseUrl = rtrim(config('app.frontend_url'), '/');

        $checkout = $user->checkout([$course->stripe_price_id => 1], [
            'success_url' => "{$baseUrl}/courses/{$course->slug}?status=success",
            'cancel_url' => "{$baseUrl}/courses/{$course->slug}?status=cancelled",
            'metadata' => [
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
            ],
        ]);

        return new CheckoutResultData(
            status: CheckoutStatus::PAYMENT_REQUIRED,
            courseId: $course->id,
            checkoutUrl: $checkout->url
        );
    }
}
